<?php

namespace Database\Seeders;

/**
 * Seeder for Contract Type system - creates 39 predefined contract types per company
 */

use Illuminate\Database\Seeder;

class ContractTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        // Predefined list of 39 comprehensive contract types
        $contracts = [
            'Employment contract',
            'Service contract',
            'Construction contract',
            'Sales contract',
            'Lease contract',
            'Purchase agreement',
            'Partnership agreement',
            'Non-disclosure agreement',
            'Non-compete agreement',
            'Joint venture agreement',
            'Franchise agreement',
            'Loan agreement',
            'License agreement',
            'Consulting agreement',
            'Distribution agreement',
            'Supply agreement',
            'Indemnification agreement',
            'Guarantee agreement',
            'Insurance contract',
            'Agency agreement',
            'Master service agreement',
            'Subcontractor agreement',
            'Operating agreement',
            'Shareholders agreement',
            'Employee handbook',
            'Independent contractor agreement',
            'Subscription agreement',
            'Software license agreement',
            'Terms of use',
            'Privacy policy',
            'End-user license agreement',
            'Service level agreement',
            'Maintenance agreement',
            'Support agreement',
            'Professional services agreement',
            'Statement of work',
            'Memorandum of understanding',
            'Letter of intent',
            'Memorandum of agreement'
        ];

        // Bulk insert all 39 contract types using array_map for efficiency
        \App\Models\ContractType::insert(
            array_map(function ($value) use ($companyId) {
                return [
                    'company_id' => $companyId,    // Specific company
                    'name' => $value               // Contract type name from predefined list
                ];
            }, $contracts)
        );

    }
}