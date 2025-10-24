<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\UpdateQuickBooksSetting;
use App\Models\QuickBooksSetting;

class QuickbookSettingsController extends AccountBaseController
{
    /**
     * Constructor
     * ------------------------------------------------------------
     * Initializes controller settings such as page title and icon.
     * Adds a middleware check to ensure that only users with
     * "manage_finance_setting" permission can access QuickBooks settings.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.quickbookSettings'; // Sets the page title for the settings page
        $this->pageIcon = 'icon-settings'; // Sets an icon for the page in UI navigation

        // Middleware: checks that the logged-in user has permission to manage finance settings
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_finance_setting') !== 'all');
            return $next($request);
        });
    }

    /**
     * Update QuickBooks Settings
     * ------------------------------------------------------------
     * Updates QuickBooks API credentials and configuration based
     * on the environment (Development or Production).
     * If client credentials are changed, access tokens are reset.
     *
     * @param  UpdateQuickBooksSetting  $request  Validated form request containing updated QuickBooks credentials.
     * @return \Illuminate\Http\JsonResponse      Redirect response with success message.
     */
    public function update(UpdateQuickBooksSetting $request)
    {
        // Retrieve the first (and typically only) QuickBooks settings record
        $credential = QuickBooksSetting::first();

        /**
         * Check if the environment is set to 'Development'
         * If so, update the sandbox credentials.
         * Otherwise, update the production credentials.
         */
        if ($request->environment == 'Development') {
            // Update sandbox (test) credentials
            $credential->sandbox_client_id = $request->sandbox_client_id;
            $credential->sandbox_client_secret = $request->sandbox_client_secret;

            // If the credentials are changed, reset the access token to force reauthorization
            if ($credential->isDirty('sandbox_client_id') || $credential->isDirty('sandbox_client_secret')) {
                $credential->access_token = null;
            }
        } else {
            // Update live (production) credentials
            $credential->client_id = $request->client_id;
            $credential->client_secret = $request->client_secret;

            // Reset access token if production credentials are modified
            if ($credential->isDirty('client_id') || $credential->isDirty('client_secret')) {
                $credential->access_token = null;
            }
        }

        // Update environment (Development or Production)
        $credential->environment = $request->environment;

        // Enable or disable QuickBooks integration
        $credential->status = $request->status ? 1 : 0;

        // Save all updates to the database
        $credential->save();

        // Redirect user back to QuickBooks tab with a success message
        return Reply::redirect(route('invoice-settings.index') . '?tab=quickbooks', __('messages.updateSuccess'));
    }
}
