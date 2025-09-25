<?php

namespace App\Observers;

use App\Models\BankAccount;
use App\Models\BankTransaction;

class BankTransactionObserver
{
    // Triggered before saving a BankTransaction (either creating or updating)
    public function saving(BankTransaction $bankTransaction)
    {
        // If a user is logged in, set the last_updated_by field
        if (user()) {
            $bankTransaction->last_updated_by = user()->id;
        }
    }

    // Triggered before creating a new BankTransaction
    public function creating(BankTransaction $bankTransaction)
    {
        // If a user is logged in, set the added_by field
        if (user()) {
            $bankTransaction->added_by = user()->id;
        }

        // If a company is available, associate the transaction with the company
        if (company()) {
            $bankTransaction->company_id = company()->id;
        }
    }

    // Triggered after a BankTransaction is successfully created
    public function created(BankTransaction $bankTransaction)
    {
        // Find the related bank account
        $bankAccount = BankAccount::find($bankTransaction->bank_account_id);

        // Update the bank account balance based on transaction type
        if (!is_null($bankAccount) && !is_null($bankTransaction)) {
            $bankBalance = $bankAccount->bank_balance;

            // If type is null or 'Cr' (credit), add the amount to balance
            if (is_null($bankTransaction->type) || $bankTransaction->type == 'Cr') {
                $bankBalance += $bankTransaction->amount;
            }

            // If type is 'Dr' (debit), subtract the amount from balance
            if ($bankTransaction->type == 'Dr') {
                $bankBalance -= $bankTransaction->amount;
            }

            // Save the updated balance to the bank account
            $bankAccount->bank_balance = $bankBalance;
            $bankAccount->save();
        }
    }

}
