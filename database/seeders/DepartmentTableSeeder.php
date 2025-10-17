<?php

namespace Database\Seeders;

/**
 * Department Table Seeder - creates 6 departments + 5 designations + links to leave types
 * Essential HR structure for company organization
 */

use App\Models\Designation;
use App\Models\LeaveType;
use App\Models\Team;
use Illuminate\Database\Seeder;

class DepartmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $this->command->info("   📁 Creating departments & designations for company {$companyId}...");

        // === 1. CREATE 6 CORE DEPARTMENTS ===
        $departments = [
            ['team_name' => 'Marketing', 'company_id' => $companyId],           // Brand & campaigns
            ['team_name' => 'Sales', 'company_id' => $companyId],              // Revenue generation
            ['team_name' => 'Human Resources', 'company_id' => $companyId],    // Employee management
            ['team_name' => 'Public Relations', 'company_id' => $companyId],   // Media & reputation
            ['team_name' => 'Research', 'company_id' => $companyId],           // R&D innovation
            ['team_name' => 'Finance', 'company_id' => $companyId],            // Accounting & budgeting
        ];

        Team::insert($departments);                    // Bulk insert departments
        $this->command->info("      ✓ 6 departments created");

        // === 2. CREATE 5 CORE DESIGNATIONS ===
        $designations = [
            ['name' => 'Trainee', 'company_id' => $companyId],                 // Entry-level
            ['name' => 'Junior', 'company_id' => $companyId],                  // 1-3 years experience
            ['name' => 'Senior', 'company_id' => $companyId],                  // 3-5 years experience
            ['name' => 'Team Lead', 'company_id' => $companyId],               // Team supervisor
            ['name' => 'Project Manager', 'company_id' => $companyId],         // Project oversight
        ];

        Designation::insert($designations);            // Bulk insert designations
        $this->command->info("      ✓ 5 designations created");

        // === 3. LINK TO LEAVE TYPES ===
        $teamIds = Team::where('company_id', $companyId)->pluck('id')->toArray();
        $designationIds = Designation::where('company_id', $companyId)->pluck('id')->toArray();

        LeaveType::where('company_id', $companyId)->update([
            'department' => json_encode($teamIds),             // JSON array of department IDs
            'designation' => json_encode($designationIds),     // JSON array of designation IDs
        ]);

        $this->command->info("      ✓ Leave types linked to departments & designations");
        $this->command->info("      📊 Total: " . count($departments) . " teams + " . count($designations) . " roles");
    }
}