<?php

namespace Database\Seeders;

/**
 * Core Database Seeder - creates essential system settings for new installations
 */

use App\Models\AwardIcon;
use App\Models\DatabaseBackupSetting;
use App\Models\GdprSetting;
use App\Models\LanguageSetting;
use App\Models\PusherSetting;
use App\Models\PushNotificationSetting;
use App\Models\SocialAuthSetting;
use App\Models\StorageSetting;
use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use App\Models\TranslateSetting;
use Illuminate\Database\Seeder;

class CoreDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->dashboardBackupSetting();      // Database backup configuration
        $this->fileStorageSetting();          // File storage settings
        $this->gdprSetting();                 // GDPR compliance settings
        $this->languageSettings();            // Multi-language support
        $this->socialAuth();                  // Social login settings
        $this->appreciationIcon();            // Award icons for appreciation system
        TranslateSetting::create(['google_key' => null]); // Google Translate API (disabled)
        $this->pushNotification();            // Push notification settings
        GlobalPaymentGatewayCredentials::create(); // Global payment gateway setup
    }

    /**
     * Create default database backup settings (disabled)
     */
    public function dashboardBackupSetting()
    {
        $backupSetting = new DatabaseBackupSetting();
        $backupSetting->status = 'inactive';                    // Backup feature disabled
        $backupSetting->hour_of_day = '';                       // No scheduled hour
        $backupSetting->backup_after_days = '0';                // Keep all backups
        $backupSetting->delete_backup_after_days = '0';         // Never auto-delete backups
        $backupSetting->save();
    }

    /**
     * Create default file storage settings (local filesystem)
     */
    private function fileStorageSetting()
    {
        $storage = new StorageSetting();
        $storage->filesystem = 'local';                         // Use local disk storage
        $storage->status = 'enabled';                           // Storage system active
        $storage->save();
    }

    /**
     * Create default GDPR compliance settings
     */
    private function gdprSetting()
    {
        $gdpr = new GdprSetting();
        $gdpr->create();                                        // Use model's default values
    }

    /**
     * Insert all supported languages from LanguageSetting::LANGUAGES constant
     */
    private function languageSettings()
    {
        LanguageSetting::insert(LanguageSetting::LANGUAGES);    // Bulk insert predefined languages
    }

    /**
     * Create default social authentication settings (all disabled)
     */
    private function socialAuth()
    {
        SocialAuthSetting::create([
            'facebook_status' => 'disable',                       // Facebook login disabled
            'google_status' => 'disable',                         // Google login disabled
            'linkedin_status' => 'disable',                       // LinkedIn login disabled
            'twitter_status' => 'disable',                        // Twitter login disabled
        ]);
    }

    /**
     * Create push notification settings (OneSignal + Pusher, both disabled)
     */
    private function pushNotification()
    {
        // OneSignal push notifications (disabled)
        $slack = new PushNotificationSetting();
        $slack->onesignal_app_id = null;                        // No OneSignal App ID
        $slack->onesignal_rest_api_key = null;                  // No API key
        $slack->notification_logo = null;                       // No logo
        $slack->save();

        // Pusher real-time notifications (empty defaults)
        $pusherSetting = new PusherSetting();
        $pusherSetting->save();                                 // Use model's default values
    }

    /**
     * Create 10 predefined award icons for appreciation system
     */
    private function appreciationIcon()
    {
        // Predefined 10 award icons with Bootstrap icon names
        $icons = [
            ['title' => 'Trophy', 'icon' => 'trophy'],
            ['title' => 'Thumbs Up', 'icon' => 'hand-thumbs-up'],
            ['title' => 'Award', 'icon' => 'award'],
            ['title' => 'Book', 'icon' => 'book'],
            ['title' => 'Gift', 'icon' => 'gift'],
            ['title' => 'Watch', 'icon' => 'watch'],
            ['title' => 'Cup', 'icon' => 'cup-hot'],
            ['title' => 'Puzzle', 'icon' => 'puzzle'],
            ['title' => 'Plane', 'icon' => 'airplane'],
            ['title' => 'Money', 'icon' => 'piggy-bank'],
        ];

        // Create each award icon
        foreach ($icons as $icon) {
            AwardIcon::create($icon);                             // title + icon name
        }
    }
}