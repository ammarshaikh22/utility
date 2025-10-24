<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\PushSetting\UpdateRequest;
use App\Models\EmailNotificationSetting;
use App\Models\PushNotificationSetting;
use App\Models\User;
use App\Notifications\TestPush;

class PushNotificationController extends AccountBaseController
{
    /**
     * Constructor
     * --------------------------
     * Initializes controller properties, page title, and permission middleware.
     * Restricts access to users with 'manage_notification_setting' permission or superadmins.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.pushNotifications'; // Page title for display
        $this->activeSettingMenu = 'notification_settings'; // Highlight this menu item

        // Permission validation middleware
        $this->middleware(function ($request, $next) {
            abort_403(
                !(user()->permission('manage_notification_setting') == 'all')
                && (!user()->is_superadmin)
            );
            return $next($request);
        });
    }

    /**
     * Update Push Notification Settings
     * ----------------------------------
     * Handles updating OneSignal and Beams configuration values.
     * For non-superadmins, only push notification preferences are saved.
     * For superadmins, full system-level settings are updated.
     *
     * @param UpdateRequest $request - validated push notification settings request
     * @param int $id - setting record ID (not always used)
     * @return \Illuminate\Http\JsonResponse
     */
    // phpcs:ignore (ignore formatting rule)
    public function update(UpdateRequest $request, $id)
    {
        // For normal users (non-superadmins), update only preference-level settings
        if (!user()->is_superadmin) {
            $this->savePushNotificationSettings($request);
        }

        // For superadmins, update full global push settings (OneSignal & Beams)
        if (user()->is_superadmin) {
            $setting = PushNotificationSetting::first();
            $setting->onesignal_app_id = $request->onesignal_app_id;
            $setting->onesignal_rest_api_key = $request->onesignal_rest_api_key;

            // Set push and beams activation statuses
            $setting->status = ($request->has('status') ? $request->status : 'inactive');
            $setting->beams_push_status = ($request->has('beams_push_status') ? $request->beams_push_status : 'inactive');

            // Beams-specific configuration values
            $setting->instance_id = $request->instance_id;
            $setting->beam_secret = $request->beam_secret;

            // Save to database
            $setting->save();
        }

        // Clear cached notification settings to ensure fresh config is used
        session()->forget('email_notification_setting');
        cache()->forget('push_setting');

        // Return success response
        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Send Test Push Notification
     * --------------------------------
     * Sends a sample push notification to the logged-in user
     * to verify configuration and connectivity with the push service.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendTestNotification()
    {
        // Get the current logged-in user
        $user = User::findOrFail($this->user->id);

        // Get active push notification settings
        $setting = PushNotificationSetting::first();

        // Send a test push notification
        // (Currently sends regardless of Beams status, but can be customized)
        if ($setting->beams_push_status == 'active') {
            $user->notify(new TestPush());
        } else {
            $user->notify(new TestPush());
        }

        // Return success response
        return Reply::success('Test notification sent.');
    }

    /**
     * Save Push Notification Preferences for Email Notifications
     * ------------------------------------------------------------
     * Updates which email notifications are also sent as push notifications.
     * This is typically called for non-superadmin users managing their preferences.
     *
     * @param \Illuminate\Http\Request $request - request object containing push settings
     * @return void
     */
    public function savePushNotificationSettings($request)
    {
        // Reset all push notifications to 'no' (disabled)
        EmailNotificationSetting::where('send_push', 'yes')->update(['send_push' => 'no']);

        // Re-enable push notifications for selected notification types
        if ($request->send_push) {
            EmailNotificationSetting::whereIn('id', $request->send_push)->update(['send_push' => 'yes']);
        }
    }
}
