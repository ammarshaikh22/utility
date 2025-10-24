<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\PusherSetting\UpdateRequest;
use App\Models\PusherSetting;
use App\Traits\pusherConfigTrait;
use Pusher\Pusher;

class PusherSettingsController extends AccountBaseController
{
    // Include reusable Pusher configuration methods from trait
    use pusherConfigTrait;

    /**
     * Constructor
     * --------------------------
     * Sets up page title, icon, and permission middleware.
     * The middleware ensures that only users with 'manage_notification_setting'
     * permission (or super admins) can access these routes.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.pusherSettings'; // Page title for the UI
        $this->pageIcon = 'icon-settings'; // Page icon for display

        // Permission check middleware
        $this->middleware(function ($request, $next) {
            abort_403(
                user()->permission('manage_notification_setting') !== 'all'
                && (!user()->is_superadmin)
            );

            return $next($request);
        });
    }

    /**
     * Update Pusher Settings
     * --------------------------
     * Validates and saves the Pusher configuration settings.
     * It first checks the credentials by sending a test message
     * using the Pusher API before saving the details to the database.
     *
     * @param UpdateRequest $request - validated input data
     * @param int $id - pusher setting record ID
     * @return \Illuminate\Http\JsonResponse
     */
    // phpcs:ignore (ignores code formatting warnings for this function)
    public function update(UpdateRequest $request, $id)
    {
        // If the status is 'active', verify the provided Pusher credentials
        if ($request->status == 'active') {
            // Initialize a new Pusher instance with provided credentials
            $checkPusher = new Pusher(
                $request->pusher_app_key,
                $request->pusher_app_secret,
                $request->pusher_app_id,
                [
                    'cluster' => $request->pusher_cluster,
                    'useTLS' => $request->force_tls
                ]
            );

            try {
                // Try sending a test trigger to verify credentials
                $checkPusher->trigger('test-pusher-channel', 'test-pusher-message', [
                    'message' => 'done'
                ]);
            } catch (\Exception $e) {
                // If verification fails, return the error message
                return Reply::dataOnly(['error' => $e->getMessage()]);
            }
        }

        // Retrieve current Pusher settings from database/helper
        $pusher = pusher_settings();

        // Update settings with request data
        $pusher->pusher_app_id = $request->pusher_app_id;
        $pusher->pusher_app_key = $request->pusher_app_key;
        $pusher->pusher_app_secret = $request->pusher_app_secret;
        $pusher->pusher_cluster = $request->pusher_cluster;
        $pusher->force_tls = $request->force_tls;

        // Convert status and feature toggles into boolean integers
        $pusher->status = $request->status == 'active' ? 1 : 0;
        $pusher->taskboard = $request->taskboard ? 1 : 0;
        $pusher->messages = $request->messages ? 1 : 0;

        // Save updated settings to database
        $pusher->save();

        // Store updated Pusher settings in the current session
        session(['pusher_settings' => PusherSetting::first()]);

        // Return success message with updated status
        return Reply::successWithData(__('messages.updateSuccess'), [
            'status' => $pusher->status
        ]);
    }
}
