<?php

namespace Database\Seeders;

/**
 * Seeder for Bank Account system - creates 2 bank accounts per company
 */

use App\Models\BankAccount;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        // Get first currency ID for this company
        $currencyId = Currency::where('company_id', $companyId)->first()->id;

        // Start database transaction for data consistency
        DB::beginTransaction();

        // Predefined bank account names
        $bankAccounts = ['Primary Account', 'Secondary Account'];

        // Create 2 bank accounts
        foreach ($bankAccounts as $key => $bankAccount) {
            $account = new BankAccount();
            $account->company_id    = $companyId;                       // Specific company
            $account->type          = 'bank';                           // Account type: bank
            $account->account_name  = fake()->company();                // Random company name as account holder
            $account->account_type  = 'current';                        // Current account (not savings)
            $account->currency_id   = $currencyId;                      // Company currency
            $account->contact_number = fake()->phoneNumber();           // Random phone number
            $account->opening_balance = fake()->numberBetween(10000, 99999); // Opening balance $10K-$99K
            $account->status        = 1;                                // Active status (1 = active)
            $account->bank_name     = $bankAccount;                     // Predefined bank name
            /** @phpstan-ignore-next-line */
            $account->account_number = fake()->bankAccountNumber();     // Random bank account number
            $account->save();                                           // Save individual account
        }

        // Commit transaction
        DB::commit();
    }

}