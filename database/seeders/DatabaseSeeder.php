<?php

namespace Database\Seeders;

/**
 * Main Database Seeder - orchestrates complete system seeding for fresh installations
 * Handles both SaaS (Super Admin) + Company-specific data seeding
 */

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // === PHASE 1: SYSTEM INITIALIZATION ===
        $this->initializeSystem();

        // === PHASE 2: GLOBAL DATA (Super Admin + Core) ===
        $this->seedGlobalData();

        // === PHASE 3: SAAS SETUP (Super Admin Users + Roles) ===
        $this->setupSaaS();

        // === PHASE 4: COMPANY-SPECIFIC DATA ===
        $this->seedCompanies();

        // === PHASE 5: FINALIZATION ===
        $this->finalizeSeeding();

        $this->command->info('✅ Database seeding completed successfully!');
    }

    /**
     * Phase 1: Initialize system configuration
     */
    private function initializeSystem()
    {
        $this->command->info('🔧 Phase 1: Initializing system...');
        
        // Disable notifications during seeding
        config(['app.seeding' => true]);
        
        // Generate application key
        Artisan::call('key:generate');
        $this->command->info('   ✓ Application key generated');
    }

    /**
     * Phase 2: Seed global/core data (shared across all companies)
     */
    private function seedGlobalData()
    {
        $this->command->info('🌍 Phase 2: Seeding global data...');
        
        $globalSeeders = [
            CountriesTableSeeder::class,           // 253 countries (ISO 3166-1)
            SmtpSettingsSeeder::class,             // Email configuration
            CoreDatabaseSeeder::class,             // Core settings (GDPR, storage, etc.)
            CoreSuperAdminDatabaseSeeder::class,   // SaaS packages, currencies, themes
            ModulePermissionSeeder::class,         // System module permissions
            OrganisationSettingsTableSeeder::class,// Organization settings
            PackageTableSeeder::class,             // SaaS packages
            FrontSeeder::class,                    // Frontend settings
            GlobalCurrencyFormatSetting::class,    // Currency formatting
        ];

        foreach ($globalSeeders as $seeder) {
            $this->command->info("   📦 Seeding: " . class_basename($seeder));
            $this->call($seeder);
        }
    }

    /**
     * Phase 3: Setup SaaS Super Admin (only for non-codecanyon)
     */
    private function setupSaaS()
    {
        if (App::environment('codecanyon')) {
            return;
        }

        $this->command->info('👑 Phase 3: Setting up SaaS Super Admin...');
        
        $this->call(SuperAdminRoleTableSeeder::class);     // Super admin roles
        $this->call(SuperAdminUsersTableSeeder::class);    // Super admin users
    }

    /**
     * Phase 4: Seed all companies with complete business data
     */
    private function seedCompanies()
    {
        if (App::environment('codecanyon')) {
            return;
        }

        $this->command->info('🏢 Phase 4: Seeding companies...');
        
        $companies = Company::select('id')->get();
        
        foreach ($companies as $company) {
            $this->command->info("   🏢 Company {$company->id}: Seeding business data...");
            
            $companySeeders = [
                // Core Structure
                DepartmentTableSeeder::class,           // Departments
                UsersTableSeeder::class,               // Employees + Admins + Clients
                RoleSeeder::class,                     // Company roles
                
                // Financial
                BankAccountSeeder::class,              // 2 bank accounts
                TaxTableSeeder::class,                 // Tax rates
                
                // Projects & Contracts
                ProjectCategorySeeder::class,          // Project categories
                ProjectSeeder::class,                  // Sample projects
                ContractTypeTableSeeder::class,        // 39 contract types
                ContractTableSeeder::class,            // Sample contracts
                
                // Sales & CRM
                LeadSeeder::class,                     // Sales leads
                ProductTableSeeder::class,             // Products/Catalog
                EstimateSeeder::class,                 // Sales estimates
                
                // HR & Attendance
                LeaveSeeder::class,                    // Leave requests
                ShiftSeeder::class,                    // Work shifts
                AttendanceTableSeeder::class,          // Attendance records
                
                // Support & Communication
                TicketSettingSeeder::class,            // Ticket categories
                TicketSeeder::class,                   // Support tickets
                MessageSeeder::class,                  // Chat messages
                
                // Operations
                ExpenseSeeder::class,                  // Business expenses
                NoticesTableSeeder::class,             // Company notices
                EventTableSeeder::class,               // Company events
                
                // Employee Engagement
                AppreciationSeeder::class,             // Awards & recognitions
            ];

            foreach ($companySeeders as $seeder) {
                $this->call($seeder, false, ['companyId' => $company->id]);
            }
        }
        
        // Sync all user permissions
        Artisan::call('sync-user-permissions all');
        $this->command->info('   ✓ User permissions synchronized');
    }

    /**
     * Phase 5: Final cleanup and cache reset
     */
    private function finalizeSeeding()
    {
        $this->command->info('🧹 Phase 5: Finalizing...');
        
        // Re-enable notifications
        config(['app.seeding' => false]);
        
        // Clear all caches
        Cache::flush();
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        $this->command->info('   ✓ All caches cleared');
    }
}