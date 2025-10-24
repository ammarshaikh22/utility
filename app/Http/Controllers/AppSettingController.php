<?php

namespace App\Http\Controllers;

use App\Models\SuperAdmin\GlobalCurrency;
use DateTimeZone;
use App\Helper\Reply;
use App\Models\Company;
use App\Models\Session;
use App\Models\Currency;
use App\Models\User;
use App\Models\GlobalSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use App\Http\Requests\Admin\App\UpdateAppSetting;
use App\Models\SuperAdmin\FrontDetail;

class AppSettingController extends AccountBaseController
{
    /**
     * Constructor for the AppSettingController.
     * Initializes the parent controller, sets the page title, and defines the active setting menu.
     * Applies middleware to restrict access to users with 'all' manage_app_setting permission or superadmin privileges.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.appSettings';
        $this->activeSettingMenu = 'app_settings';

        $this->middleware(function ($request, $next) {
            // Restrict access if user lacks 'all' permission and is not a superadmin
            abort_403(user()->permission('manage_app_setting') !== 'all' && GlobalSetting::validateSuperAdmin('manage_superadmin_app_settings'));

            return $next($request);
        });
    }

    /**
     * Displays the app settings page with tabbed content.
     * Determines the active tab and renders the corresponding view (app setting, file upload, client signup, or Google map).
     * Retrieves date formats, timezones, currencies, and global settings for display.
     * Handles both AJAX and standard requests.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $tab = request('tab');

        // Set the view based on the requested tab
        switch ($tab) {
        case 'file-upload-setting':
            // Restrict access to superadmins for file upload settings
            abort_403(GlobalSetting::validateSuperAdmin('manage_superadmin_app_settings'));
            $this->view = 'app-settings.ajax.file-upload-setting';
            break;
        case 'client-signup-setting':
            // Restrict access to users with 'all' manage_app_setting permission
            abort_403(user()->permission('manage_app_setting') !== 'all');
            $this->view = 'app-settings.ajax.client-signup-setting';
            break;
        case 'google-map-setting':
            // Restrict access to superadmins for Google map settings
            abort_403(GlobalSetting::validateSuperAdmin('manage_superadmin_app_settings'));
            $this->view = 'app-settings.ajax.map-setting';
            break;
        default:
            $this->view = 'app-settings.ajax.app-setting';
            break;
        }

        // Retrieve data for the settings form
        $this->dateFormat = array_keys(Company::DATE_FORMATS);
        $this->timezones = DateTimeZone::listIdentifiers();
        $this->currencies = Currency::all();

        // Use global currencies for superadmins
        if (user()->is_superadmin) {
            $this->currencies = GlobalCurrency::all();
        }

        $this->dateObject = now();
        $this->cachedFile = File::exists(base_path('bootstrap/cache/config.php'));

        // Retrieve global settings
        $this->globalSetting = GlobalSetting::first();

        $this->activeTab = $tab ?: 'app-setting';

        // Handle AJAX requests by rendering the view and returning JSON
        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        // Render the main settings view for standard requests
        return view('app-settings.index', $this->data);
    }

    /**
     * Updates app settings based on the requested tab.
     * Delegates to specific update methods for file upload, client signup, Google map, or general app settings.
     * Clears session and cache data after updates.
     *
     * @param UpdateAppSetting $request The validated request containing update data.
     * @param mixed $id The ID of the setting (not used in this context).
     * @return array
     * @throws BindingResolutionException
     * @throws CommandNotFoundException
     */
    public function update(UpdateAppSetting $request, $id)
    {
        $tab = request('page');

        // Delegate to specific update methods based on the tab
        switch ($tab) {
        case 'file-upload-setting':
            isWorksuiteSaas() ? $this->updateFileUploadSetting($request) : '';
            break;
        case 'client-signup-setting':
            $this->updateClientSignupSetting($request);
            break;
        case 'google-map-setting':
            isWorksuiteSaas() ? $this->updateGoogleMapSetting($request) : '';
            break;
        default:
            $this->updateAppSetting($request);
            break;
        }

        // Clear session and cache
        session()->forget('company');
        cache()->forget('global_setting');
        session()->forget('companyOrGlobalSetting');

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Saves global settings for superadmins.
     * Updates currency, date/time formats, session driver, debug settings, and other global configurations.
     *
     * @param UpdateAppSetting $request The validated request containing global setting data.
     */
    public function globalSettingSave($request)
    {
        $globalSetting = GlobalSetting::first();

        $globalSetting->currency_id = $request->currency_id;
        $globalSetting->moment_format = $this->momentFormat($globalSetting->date_format);

        $globalSetting->app_debug = $request->has('app_debug') && $request->app_debug == 'on' ? 1 : 0;
        $globalSetting->system_update = $request->has('system_update') && $request->system_update == 'on' ? 1 : 0;
        $globalSetting->session_driver = $request->session_driver;
        $globalSetting->timezone = $request->timezone;
        $globalSetting->locale = $request->locale;
        $globalSetting->date_format = $request->date_format;
        $globalSetting->time_format = $request->time_format;

        // Update superadmin-specific settings
        if (user()->is_superadmin) {
            $globalSetting->company_need_approval = $request->company_need_approval == 'on' ? 1 : 0;
            $globalSetting->currency_id = $request->currency_id;
            $globalSetting->email_verification = $request->email_verification == 'on' ? 1 : 0;
        }

        $globalSetting->moment_format = $this->momentFormat($globalSetting->date_format);
        $globalSetting->datatable_row_limit = $request->datatable_row_limit;
        $globalSetting->save();
    }

    /**
     * Updates general app settings for non-superadmin users.
     * Handles currency, timezone, locale, date/time formats, and other company-specific settings.
     * Updates user locale, clears sessions, and refreshes cache.
     *
     * @param UpdateAppSetting $request The validated request containing app setting data.
     */
    public function updateAppSetting($request)
    {
        // Update company settings for non-superadmins
        if (!user()->is_superadmin) {
            $setting = company();
            $setting->currency_id = $request->currency_id;
            $setting->timezone = $request->timezone;
            $setting->locale = $request->locale;
            $setting->date_format = $request->date_format;
            $setting->time_format = $request->time_format;
            $setting->moment_format = $this->momentFormat($setting->date_format);
            $setting->dashboard_clock = $request->has('dashboard_clock') && $request->dashboard_clock == 'on' ? 1 : 0;
            $setting->employee_can_export_data = $request->has('employee_can_export_data') && $request->employee_can_export_data == 'on' ? 1 : 0;
            $setting->datatable_row_limit = $request->datatable_row_limit;
            $setting->save();
            $setting->refresh();
        }

        // Update exchange rates
        Artisan::call('update-exchange-rate');

        // Clear all sessions except the current user's
        DB::table('sessions')->where('user_id', '<>', user()->id)->delete();

        // Update the current user's locale
        $user = user();
        $user->locale = $request->locale;
        $user->saveQuietly();

        // Update global or company settings locale
        $globalSetting = companyOrGlobalSetting();
        $globalSetting->update(['locale' => $request->locale]);

        // Update front detail locale for WORKSUITESAAS
        FrontDetail::first()->update(['locale' => $request->locale]);

        // Clear RTL session
        session()->forget('isRtl');

        // Update currency format for non-superadmins
        if (!user()->is_superadmin) {
            if ($request->currency_id) {
                \session()->forget('currency_format_setting');
                currency_format_setting($setting->currency_id);
            }
        }

        // Save global settings for superadmins
        if (user()->is_superadmin) {
            $this->globalSettingSave($request);
        }

        // Refresh user session
        session(['user' => User::find($user->id)]);

        // Reset cache
        $this->resetCache();
    }

    /**
     * Updates file upload settings for superadmins.
     * Processes allowed file types, file size, and maximum number of files, then saves to global settings.
     *
     * @param UpdateAppSetting $request The validated request containing file upload setting data.
     */
    public function updateFileUploadSetting($request)
    {
        if (!empty($request->allowed_file_types)) {
            $allowed_file_types = $request->allowed_file_types;

            $fileTypeArray = [];

            foreach (json_decode($allowed_file_types) as $file) {
                $fileTypeArray[] = $file->value;
            }
        }

        $globalSetting = GlobalSetting::first();
        $globalSetting->allowed_file_types = !empty($fileTypeArray) ? implode(',', $fileTypeArray) : '';
        $globalSetting->allowed_file_size = $request->allowed_file_size;
        $globalSetting->allow_max_no_of_files = $request->allow_max_no_of_files;
        $globalSetting->save();
    }

    /**
     * Updates client signup settings.
     * Configures whether client signup is allowed and if admin approval is required, then saves to company settings.
     *
     * @param UpdateAppSetting $request The validated request containing client signup setting data.
     */
    public function updateClientSignupSetting($request)
    {
        $setting = \company();
        $setting->allow_client_signup = $request->allow_client_signup == 'on' ? 1 : 0;
        $setting->admin_client_signup_approval = $request->admin_client_signup_approval == 'on' ? 1 : 0;
        $setting->save();
    }

    /**
     * Updates Google Map settings for superadmins.
     * Saves the Google Map API key to global settings and clears the cache.
     *
     * @param UpdateAppSetting $request The validated request containing Google Map setting data.
     */
    public function updateGoogleMapSetting(UpdateAppSetting $request)
    {
        $globalSetting = \global_setting();
        $globalSetting->google_map_key = $request->google_map_key;
        $globalSetting->save();
        cache()->forget('global_setting');
    }

    /**
     * Converts a date format to a Moment.js-compatible format.
     * Returns the corresponding Moment.js format or a default if not found.
     *
     * @param string $dateFormat The date format to convert.
     * @return string The Moment.js-compatible date format.
     */
    public function momentFormat($dateFormat)
    {
        $availableDateFormats = Company::DATE_FORMATS;

        return (isset($availableDateFormats[$dateFormat])) ? $availableDateFormats[$dateFormat] : 'DD-MM-YYYY';
    }

    /**
     * Resets the application cache.
     * Clears or optimizes cache based on the request parameter, handling Artisan commands for cache management.
     *
     * @return array|string Success response or error message if an exception occurs.
     */
    public function resetCache()
    {
        if (request()->cache) {
            try {
                Artisan::call('optimize');
                Artisan::call('route:clear');
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        } else {
            Artisan::call('optimize:clear');
            Artisan::call('cache:clear');
        }

        cache()->flush();

        return Reply::success(__('messages.cacheClear'));
    }

    /**
     * Refreshes the application cache.
     * Clears and optionally re-optimizes cache, handling Artisan commands for cache management.
     *
     * @return array|string Success response or error message if an exception occurs.
     */
    public function refreshCache()
    {
        cache()->flush();

        if (File::exists(base_path('bootstrap/cache/config.php'))) {
            try {
                Artisan::call('optimize:clear');
                Artisan::call('cache:clear');
                Artisan::call('optimize');
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        } else {
            Artisan::call('optimize:clear');
            Artisan::call('cache:clear');
        }

        return Reply::success(__('messages.cacheClear'));
    }

    /**
     * Deletes sessions for specified users, excluding the current user.
     * Removes session records from the database for the provided user IDs.
     *
     * @param array $usersIds Array of user IDs whose sessions should be deleted (optional).
     * @return array Success response indicating sessions were deleted.
     */
    public function deleteSessions(array $usersIds = [])
    {
        if (!empty($usersIds)) {
            Session::whereIn('user_id', $usersIds)->where('user_id', '<>', user()->id)->delete();
        }

        return Reply::success(__('messages.deleteSuccess'));
    }
}