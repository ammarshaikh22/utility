<?php

namespace App\Observers\SuperAdmin;

use App\Models\SuperAdmin\ClientPayment;
use App\Models\Invoice;
use App\Notifications\InvoicePaymentReceived;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Notification;

class InvoicePaymentReceivedObserver
{
    // After a ClientPayment is created, notify all front-end admins about the payment
    public function created(ClientPayment $payment)
    {
        // Skip processing if running in console or database seeding
        if (isRunningInConsoleOrSeeding()) {
            return true;
        }

        try {
            // Get all front-end admins of the company
            $admins = User::frontAllAdmins($payment->company_id);

            // Find the associated invoice
            $invoice = Invoice::findOrFail($payment->invoice_id);

            // If invoice exists and is either paid or partially paid, send notification to admins
            if ($invoice && in_array($invoice->status, ['paid', 'partial'])) {
                Notification::send($admins, new InvoicePaymentReceived($invoice));
            }

        } catch (Exception $e) {
            // Log any exception that occurs during notification
            info($e->getMessage());
        }
    }
}
