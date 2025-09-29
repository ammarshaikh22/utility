<?php

namespace App\Observers;

use App\Models\BankAccount;
use App\Models\BankTransaction;

class BankAccountObserver
{
    /**
     * Triggered before saving a bank account (both create & update).
     * - Stores the user who last updated the record.
     */
    public function saving(BankAccount $bankAccount)
    {
        if (user()) {
            $bankAccount->last_updated_by = user()->id;
        }
    }

    /**
     * Triggered when a new bank account is being created.
     * - Assigns the user who created it.
     * - Associates the account with the current company.
     */
    public function creating(BankAccount $bankAccount)
    {
        if (user()) {
            $bankAccount->added_by = user()->id;
        }

        if (company()) {
            $bankAccount->company_id = company()->id;
        }
    }

    /**
     * Triggered after a bank account is created.
     * - Logs the initial opening balance as a bank transaction.
     */
    public function created(BankAccount $bankAccount)
    {
        $transaction = new BankTransaction();
        $transaction->company_id = $bankAccount->company_id;
        $transaction->bank_account_id = $bankAccount->id;
        $transaction->transaction_date = now()->format('Y-m-d');
        $transaction->amount = round($bankAccount->opening_balance, 2);
        $transaction->bank_balance = round($bankAccount->opening_balance, 2);
        $transaction->transaction_relation = 'bank';
        $transaction->title = 'bank-account-created';
        $transaction->save();
    }

    /**
     * Triggered when a bank account is updated.
     * - If the opening balance changes, logs a new transaction
     *   to reflect the adjustment.
     */
    public function updating(BankAccount $bankAccount)
    {
        if ($bankAccount->isDirty('opening_balance')) {
            $originalBalance = $bankAccount->getOriginal('opening_balance');
            $getCurrentBalance = $bankAccount->opening_balance;
            $newBalance = 0;
            $currentBankBalance = 0;

            // Fetch current account details to get latest balance
            $currentBankAccount = BankAccount::find($bankAccount->id);
            $bankBalance = $currentBankAccount->bank_balance;

            $transaction = new BankTransaction();

            // If balance decreased → Debit
            if ($originalBalance > $getCurrentBalance) {
                $newBalance = $originalBalance - $getCurrentBalance;
                $transaction->type = 'Dr';
                $currentBankBalance = $bankBalance - $newBalance;
            }

            // If balance increased → Credit
            if ($originalBalance < $getCurrentBalance) {
                $newBalance = $getCurrentBalance - $originalBalance;
                $currentBankBalance = $bankBalance + $newBalance;
            }

            // Save adjustment transaction
            $transaction->bank_account_id = $bankAccount->id;
            $transaction->amount = round($newBalance, 2);
            $transaction->transaction_date = now()->format('Y-m-d');
            $transaction->bank_balance = round($currentBankBalance, 2);
            $transaction->transaction_relation = 'bank';
            $transaction->title = 'bank-account-updated';
            $transaction->save();
        }
    }
}
