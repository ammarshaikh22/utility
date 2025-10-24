<?php

namespace Database\Seeders;

/**
 * Seeder for Contract system - creates configurable number of contracts per company
 */

use App\Models\Company;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContractTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        // Get company settings
        $setting = Company::find($companyId);
        
        // Get admin user ID for this company
        $admin = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'admin')              // Only admin role
            ->where('users.company_id', $companyId)     // Specific company
            ->select('users.id')
            ->first();

        // Get configurable seed count from config/app.php
        $count = config('app.seed_record_count');
        $faker = \Faker\Factory::create();

        // Create contracts using Factory, then modify each one
        Contract::factory()
            ->count((int)$count)                        // Create specified number of contracts
            ->make()                                    // Generate but don't save yet
            ->each(function (Contract $contract) use ($faker, $admin, $setting, $companyId) {
                $contract->company_id = $companyId;                         // Specific company
                $contract->contract_type_id = $faker->randomElement($this->getContractType($companyId)); // Random contract type
                $contract->client_id = $this->getClient($companyId);        // Random client
                $contract->added_by = $admin->id;                           // Admin added this
                $contract->currency_id = $setting->currency_id;             // Company currency
                $contract->contract_number = Contract::where('company_id', $companyId)->count() + 1; // Sequential number
                $contract->save();                                          // Save modified contract
            });
    }

    /**
     * Get random contract type IDs for this company
     */
    public function getContractType($companyId)
    {
        return \App\Models\ContractType::inRandomOrder()
            ->where('company_id', $companyId)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get random CLIENT user ID for this company
     */
    public function getClient($companyId)
    {
        /** @phpstan-ignore-next-line */
        return \App\Models\User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftJoin('client_details', 'users.id', '=', 'client_details.user_id') // Optional client details
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'client')                 // Only client role
            ->where('users.company_id', $companyId)         // Specific company
            ->inRandomOrder()
            ->first()->user_id;                             // Return random client user_id
    }
}