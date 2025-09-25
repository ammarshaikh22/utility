<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Payment;
use App\Events\NewPaymentEvent;
use App\Scopes\ActiveScope;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Events\InvoicePaymentReceivedEvent;
use App\Http\Controllers\QuickbookController;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Traits\EmployeeActivityTrait;

class PaymentObserver
{
    use EmployeeActivityTrait;

    // Before saving a payment, update last_updated_by
    public function saving(Payment $payment)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $payment->last_updated_by = user()->id;
        }
    }

    // Before creating a new payment record
    public function creating(Payment $payment)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $payment->added_by = user() ? user()->id : null;
        }

        // Assign company_id from related currency
        $payment->company_id = $payment->currency?->company_id;
    }

    // After a payment is saved
    public function saved(Payment $payment)
    {
        if (isRunningInConsoleOrSeeding()) {
            return;
        }

        // Notify client & admins if payment is complete and not offline
        if (($payment->project_id && $payment->project->client_id != null) || ($payment->invoice_id && $payment->invoice->client_id != null) && $payment->gateway != 'Offline') {
            $clientId = ($payment->project_id && $payment->project->client_id != null) ? $payment->project->client_id : $payment->invoice->client_id;

            $admins = User::allAdmins($payment->company->id);
            $client_details = User::withoutGlobalScope(ActiveScope::class)->where('id', $clientId)->get();
            $notifyUser = $client_details;
            $notifyUsers = $notifyUser->merge($admins);

            if ($notifyUser && $payment->status === 'complete') {
                event(new NewPaymentEvent($payment, $notifyUsers));
            }
        }
    }

    // After a payment is created
    public function created(Payment $payment)
    {
        // Update invoice status & due amount
        if (($payment->invoice_id || $payment->order_id) && $payment->status == 'complete') {

            if ($payment->invoice_id) {
                $invoice = $payment->invoice;
            }
            elseif ($payment->order_id) {
                $invoice = Invoice::where('order_id', $payment->invoice_id)->latest()->first();
            }

            $due = 0;

            if (isset($payment->invoice)) {
                $due = $payment->invoice->due_amount;
            }
            elseif (isset($payment->order)) {
                $due = $payment->order->total;
            }

            $dueAmount = $due - $payment->amount;

            if ($dueAmount > 0) {
                $payment->invoice->status = 'partial';
            } else {
                $payment->invoice->status = 'paid';
            }

            if (isset($invoice)) {
                $invoice->due_amount = $dueAmount;
            }

            $invoice->save();

            // Trigger payment received events & QuickBooks sync
            try {
                if (!isRunningInConsoleOrSeeding()) {

                    if ($payment->gateway != 'Offline') {
                        event(new InvoicePaymentReceivedEvent($payment));
                    }

                    if (quickbooks_setting()->status && quickbooks_setting()->access_token != '') {
                        $quickBooks = new QuickbookController();
                        $qbPaymentId = $quickBooks->createPayment($payment);

                        $payment->quickbooks_payment_id = $qbPaymentId;
                        $payment->saveQuietly();
                    }
                }
            } catch (Exception $e) {
                Log::info($e);
            }
        }

        // Create bank transaction record when payment is credited
        if (!is_null($payment->bank_account_id) && $payment->status == 'complete') {

            $bankAccount = BankAccount::find($payment->bank_account_id);
            $bankBalance = $bankAccount->bank_balance;
            $totalBalance = $bankBalance + $payment->amount;

            $transaction = new BankTransaction();
            $transaction->company_id = $payment->company_id;
            $transaction->bank_account_id = $payment->bank_account_id;
            $transaction->payment_id = $payment->id;
            $transaction->invoice_id = $payment->invoice_id;
            $transaction->amount = round($payment->amount, 2);
            $transaction->transaction_date = $payment->paid_on;
            $transaction->bank_balance = round($totalBalance);
            $transaction->transaction_relation = 'payment';
            $transaction->transaction_related_to = $payment->id;
            $transaction->title = 'payment-credited';
            $transaction->save();
        }
    }

    // Before updating a payment
    public function updating(Payment $payment)
    {
        if (isRunningInConsoleOrSeeding()) {
            return true;
        }

        // Handle changes in bank account, amount, or status
        if (!is_null($payment->bank_account_id) && $payment->status == 'complete') {
            // ... (logic for handling old/new bank accounts and amount changes)
        }

        // Reverse amount if status changes to not complete
        if ($payment->isDirty('status') && $payment->status != 'complete') {
            // ... (logic for debit transaction on status change)
        }
    }

    // After updating a payment
    public function updated(Payment $payment)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'payment-updated', $payment->id, 'payment');
        }
    }

    // Before deleting a payment
    public function deleting(Payment $payment)
    {
        // Reverse bank account balance and log transaction
        if (!is_null($payment->bank_account_id) && $payment->status == 'complete') {
            // ... (logic for debit and update balance)
        }

        // Update invoice status if exists
        if ($payment->invoice) {
            // ... (update invoice status and due amount)
        }

        // Reset order status if linked
        if ($payment->order_id) {
            $order = Order::findOrFail($payment->order_id);
            $order->status = 'pending';
            $order->save();
        }

        // Remove QuickBooks entry if synced
        if (!is_null($payment->quickbooks_payment_id)) {
            if (quickbooks_setting()->status && quickbooks_setting()->access_token != '') {
                $quickBooks = new QuickbookController();
                $quickBooks->deletePayment($payment);
            }
        }

        // Remove related notifications
        $notifyData = ['App\Notifications\NewPayment', 'App\Notifications\PaymentReminder'];
        Notification::deleteNotification($notifyData, $payment->id);
    }

    // After a payment is deleted
    public function deleted(Payment $payment)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'payment-deleted');
        }
    }
}
