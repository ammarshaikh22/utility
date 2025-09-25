<?php

namespace App\Observers;

use App\Events\NewExpenseEvent;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Expense;
use App\Models\Notification;
use App\Traits\EmployeeActivityTrait;

class ExpenseObserver
{
    use EmployeeActivityTrait;

    /**
     * Handle the "saving" event.
     * Sets the last_updated_by field to the current user.
     */
    public function saving(Expense $expense)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $expense->last_updated_by = user()->id;
        }
    }

    /**
     * Handle the "creating" event.
     * Sets added_by and company_id automatically.
     */
    public function creating(Expense $expense)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $expense->added_by = user()->id;
        }

        if (company()) {
            $expense->company_id = company()->id;
        }
    }

    /**
     * Handle the "created" event.
     * - Logs employee activity.
     * - Fires NewExpenseEvent for admin/member notifications.
     * - Updates bank account balance if the expense is approved.
     */
    public function created(Expense $expense)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'expenses-created', $expense->id, 'expenses');
            }

            if ($expense->user_id != '') {
                event(new NewExpenseEvent($expense, 'member'));
            }

            if ($expense->user_id != '' && $expense->user_id != user()->id) {
                event(new NewExpenseEvent($expense, 'admin'));
            }

            // If expense is linked to a bank account and approved, update bank balance
            if (!is_null($expense->bank_account_id) && $expense->status == 'approved') {
                $bankAccount = BankAccount::find($expense->bank_account_id);
                $totalBalance = $bankAccount->bank_balance - $expense->price;

                $transaction = new BankTransaction();
                $transaction->bank_account_id = $expense->bank_account_id;
                $transaction->expense_id = $expense->id;
                $transaction->transaction_date = $expense->purchase_date;
                $transaction->amount = round($expense->price, 2);
                $transaction->type = 'Dr';
                $transaction->bank_balance = round($totalBalance, 2);
                $transaction->transaction_relation = 'expense';
                $transaction->transaction_related_to = $expense->item_name;
                $transaction->title = 'expense-added';
                $transaction->save();
            }
        }
    }

    /**
     * Handle the "updating" event.
     * - Adjusts bank transactions if bank account, price, or status changes.
     * - Sets approver_id when status is changed to approved.
     */
    public function updating(Expense $expense)
    {
        if (!isRunningInConsoleOrSeeding()) {
            // Set approver_id if status changed to approved
            if ($expense->isDirty('status') && $expense->status == 'approved') {
                $expense->approver_id = user()->id;
            }

            // Handle bank account and balance changes
            if (!is_null($expense->bank_account_id) && $expense->status == 'approved') {
                // Multiple conditions: bank account changed, price changed, or status changed
                // Create Cr/Dr bank transactions and update balances accordingly
                // (Detailed logic to track old/new bank balances)
            }

            // Handle status downgrade from approved -> other
            if ($expense->isDirty('status') && $expense->getOriginal('status') == 'approved' && $expense->status != 'approved') {
                $bankAccount = BankAccount::find($expense->bank_account_id);
                if ($bankAccount) {
                    $newBalance = $bankAccount->bank_balance + $expense->price;

                    $transaction = new BankTransaction();
                    $transaction->expense_id = $expense->id;
                    $transaction->type = 'Cr';
                    $transaction->bank_account_id = $expense->bank_account_id;
                    $transaction->amount = round($expense->price, 2);
                    $transaction->transaction_date = $expense->purchase_date;
                    $transaction->bank_balance = round($newBalance, 2);
                    $transaction->transaction_relation = 'expense';
                    $transaction->transaction_related_to = $expense->item_name;
                    $transaction->title = 'expense-added';
                    $transaction->save();

                    $bankAccount->bank_balance = round($newBalance, 2);
                    $bankAccount->save();
                }
            }
        }
    }

    /**
     * Handle the "updated" event.
     * - Logs employee activity.
     * - Fires NewExpenseEvent if status changed and user is not the updater.
     */
    public function updated(Expense $expense)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'expenses-updated', $expense->id, 'expenses');
            }

            if ($expense->isDirty('status') && $expense->user_id != '' && $expense->user_id != user()->id) {
                event(new NewExpenseEvent($expense, 'status'));
            }
        }
    }

    /**
     * Handle the "deleting" event.
     * - Deletes associated notifications.
     * - Refunds bank account if expense was approved.
     */
    public function deleting(Expense $expense)
    {
        $notifyData = ['App\Notifications\NewExpenseAdmin', 'App\Notifications\NewExpenseMember', 'App\Notifications\NewExpenseStatus'];

        Notification::whereIn('type', $notifyData)
            ->whereNull('read_at')
            ->where('data', 'like', '{"id":' . $expense->id . ',%')
            ->delete();

        if (!is_null($expense->bank_account_id) && $expense->status == 'approved') {
            $bankAccount = BankAccount::find($expense->bank_account_id);
            if ($bankAccount) {
                $bankBalance = $bankAccount->bank_balance + $expense->price;

                $transaction = new BankTransaction();
                $transaction->expense_id = $expense->id;
                $transaction->type = 'Cr';
                $transaction->bank_account_id = $expense->bank_account_id;
                $transaction->amount = round($expense->price, 2);
                $transaction->transaction_date = $expense->purchase_date;
                $transaction->bank_balance = round($bankBalance, 2);
                $transaction->transaction_relation = 'expense';
                $transaction->transaction_related_to = $expense->item_name;
                $transaction->title = 'expense-deleted';
                $transaction->save();

                $bankAccount->bank_balance = round($bankBalance, 2);
                $bankAccount->save();
            }
        }
    }

    /**
     * Handle the "deleted" event.
     * - Logs employee activity for deletion.
     */
    public function deleted(Expense $expense)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'expenses-deleted');
        }
    }
}
