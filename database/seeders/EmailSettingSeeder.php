<?php

namespace Database\Seeders;

/**
 * Email Settings Seeder - configures 4 essential email notification templates
 * Controls core system events that trigger automated emails to users
 */

use App\Models\EmailNotificationSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmailSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('   📧 Seeding core email notification settings...');

        // 4 essential email notification templates for MVP
        $notificationSettings = [
            // === CORE USER EVENTS ===
            [
                'setting_name' => 'User Registration/Added by Admin',
                'send_email' => 'yes',                    // Welcome email for new users
                'slug' => Str::slug('User Registration/Added by Admin')
            ],
            
            // === PROJECT MANAGEMENT ===
            [
                'setting_name' => 'Employee Assign to Project',
                'send_email' => 'yes',                    // Project assignment notification
                'slug' => Str::slug('Employee Assign to Project')
            ],
            
            // === COMPANY COMMUNICATION ===
            [
                'setting_name' => 'New Notice Published',
                'send_email' => 'no',                     // Push notification only (less spam)
                'slug' => Str::slug('New Notice Published')
            ],
            
            // === TASK MANAGEMENT ===
            [
                'setting_name' => 'User Assign to Task',
                'send_email' => 'yes',                    // Task assignment notification
                'slug' => Str::slug('User Assign to Task')
            ],
        ];

        // Bulk insert all 4 email settings
        EmailNotificationSetting::insert($notificationSettings);
        
        $this->command->info("      ✓ 4 email templates created");
        $this->command->info("      📊 Enabled: " . collect($notificationSettings)->where('send_email', 'yes')->count() . " | Disabled: " . collect($notificationSettings)->where('send_email', 'no')->count());
    }
}