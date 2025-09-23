<?php

namespace App\Helper;

use App\Models\User;
use App\Helper\Files;
use App\Models\AttendanceSetting;
use App\Models\Company;
use App\Models\Currency;
use App\Models\CustomLinkSetting;
use App\Models\GdprSetting;
use App\Models\InvoiceSetting;
use App\Models\LanguageSetting;
use App\Models\LogTimeFor;
use App\Models\Permission;
use App\Models\QuickBooksSetting;
use App\Models\SocialAuthSetting;
use App\Models\StorageSetting;
use Illuminate\Support\Str;
use App\Models\ThemeSetting;
use App\Scopes\CompanyScope;
use Carbon\Carbon;
use App\Models\UserPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\SuperAdmin\GlobalCurrency;
use App\Models\SuperAdmin\GlobalInvoiceSetting;

/**
 * Get the current logged-in user.
 *
 * @return mixed|null The user object or null if not logged in
 */
if (!function_exists('user')) {
    function user()
    {
        if (session()->has('user')) {
            return session('user');
        }

        $authId = auth()->id();

        if ($authId) {
            if (session()->has('company')) {
                $user = User::where('user_auth_id', $authId)->where('status', 'active')->first();
            } else {
                $user = DB::table('users')->where('user_auth_id', $authId)->where('status', 'active')->first();
                return $user;
            }

            if ($user) {
                if (session()->has('clientContact')) {
                    session()->forget('clientContact');
                }

                if (!is_null($user->is_client_contact)) {
                    session(['clientContact' => $user->clientContact]);
                    $user = $user->clientContact->client;
                }
                session(['user' => $user]);
                return session('user');
            } else {
                auth()->logout();
            }
        }

        return null;
    }
}

/**
 * Get the roles of the current logged-in user.
 *
 * @return array|null The array of role names or null if no user
 */
if (!function_exists('user_roles')) {
    function user_roles()
    {
        if (session()->has('user_roles')) {
            return session('user_roles');
        }

        $user = user();

        if ($user) {
            if (!isset(user()->roles)) {
                session(['user' => User::find(user()->id)]);
            }

            $roles = user()->roles;
            session(['user_roles' => $roles->pluck('name')->toArray()]);
            session(['user_role_ids' => $roles->pluck('id')->toArray()]);

            return session('user_roles');
        }

        return null;
    }
}

/**
 * Get the subdomain schema (http or https).
 *
 * @return string The schema (http or https)
 */
if (!function_exists('getSubdomainSchema')) {
    function getSubdomainSchema()
    {
        if (!session()->has('subdomain_schema')) {
            if (\Illuminate\Support\Facades\Schema::hasTable('sub_domain_module_settings')) {
                $data = \Illuminate\Support\Facades\DB::table('sub_domain_module_settings')->first();
            }

            session(['subdomain_schema' => isset($data->schema) ? $data->schema : 'http']);
        }

        return session('subdomain_schema');
    }
}

/**
 * Get the superadmin theme settings.
 *
 * @return \App\Models\ThemeSetting The superadmin theme settings
 */
if (!function_exists('superadmin_theme')) {
    function superadmin_theme()
    {
        if (!session()->has('superadmin_theme')) {
            session(['superadmin_theme' => \App\Models\ThemeSetting::withoutGlobalScope(CompanyScope::class)->where('panel', 'superadmin')->first()]);
        }

        return session('superadmin_theme');
    }
}

/**
 * Get the admin theme settings.
 *
 * @return \App\Models\ThemeSetting The admin theme settings
 */
if (!function_exists('admin_theme')) {
    function admin_theme()
    {
        if (!session()->has('admin_theme')) {
            if (superadmin_theme()->restrict_admin_theme_change) {
                session(['admin_theme' => superadmin_theme()]);
            } else {
                session(['admin_theme' => ThemeSetting::where('panel', 'admin')->first()]);
            }
        }

        return session('admin_theme');
    }
}

/**
 * Get the employee theme settings.
 *
 * @return \App\Models\ThemeSetting The employee theme settings
 */
if (!function_exists('employee_theme')) {
    function employee_theme()
    {
        if (!session()->has('employee_theme')) {
            if (superadmin_theme()->restrict_admin_theme_change) {
                session(['employee_theme' => superadmin_theme()]);
            } else {
                session(['employee_theme' => ThemeSetting::where('panel', 'employee')->first()]);
            }
        }

        return session('employee_theme');
    }
}

/**
 * Get the client theme settings.
 *
 * @return \App\Models\ThemeSetting The client theme settings
 */
if (!function_exists('client_theme')) {
    function client_theme()
    {
        if (!session()->has('client_theme')) {
            if (superadmin_theme()->restrict_admin_theme_change) {
                session(['client_theme' => superadmin_theme()]);
            } else {
                session(['client_theme' => ThemeSetting::where('panel', 'client')->first()]);
            }
        }

        return session('client_theme');
    }
}

/**
 * Get the global settings.
 *
 * @return \App\Models\GlobalSetting The global settings
 */
if (!function_exists('global_setting')) {
    function global_setting()
    {
        if (!cache()->has('global_setting')) {
            $setting = \App\Models\GlobalSetting::first();
            cache(['global_setting' => $setting]);
            return $setting;
        }

        return cache('global_setting');
    }
}

/**
 * Get the push notification settings.
 *
 * @return \App\Models\PushNotificationSetting The push notification settings
 */
if (!function_exists('push_setting')) {
    function push_setting()
    {
        if (!cache()->has('push_setting')) {
            cache(['push_setting' => \App\Models\PushNotificationSetting::first()]);
        }

        return cache('push_setting');
    }
}

/**
 * Get the enabled language settings.
 *
 * @return \Illuminate\Database\Eloquent\Collection The enabled language settings
 */
if (!function_exists('language_setting')) {
    function language_setting()
    {
        if (!cache()->has('language_setting')) {
            cache(['language_setting' => \App\Models\LanguageSetting::where('status', 'enabled')->get()]);
        }

        return cache('language_setting');
    }
}

/**
 * Get the language settings for a specific locale.
 *
 * @param string $locale The language code
 * @return \App\Models\LanguageSetting The language settings for the locale
 */
if (!function_exists('language_setting_locale')) {
    function language_setting_locale($locale)
    {
        if (!cache()->has('language_setting_' . $locale)) {
            cache(['language_setting_' . $locale => \App\Models\LanguageSetting::where('language_code', $locale)->first()]);
        }

        return cache('language_setting_' . $locale);
    }
}

/**
 * Get the SMTP settings.
 *
 * @return \App\Models\SmtpSetting The SMTP settings
 */
if (!function_exists('smtp_setting')) {
    function smtp_setting()
    {
        if (!session()->has('smtp_setting')) {
            session(['smtp_setting' => \App\Models\SmtpSetting::first()]);
        }

        return session('smtp_setting');
    }
}

/**
 * Get the message settings.
 *
 * @return \App\Models\MessageSetting The message settings
 */
if (!function_exists('message_setting')) {
    function message_setting()
    {
        if (!session()->has('message_setting')) {
            session(['message_setting' => \App\Models\MessageSetting::first()]);
        }

        return session('message_setting');
    }
}

/**
 * Get the enabled storage settings.
 *
 * @return \App\Models\StorageSetting The enabled storage settings
 */
if (!function_exists('storage_setting')) {
    function storage_setting()
    {
        if (!session()->has('storage_setting')) {
            $setting = StorageSetting::where('status', 'enabled')->first();
            session(['storage_setting' => $setting]);
        }

        return session('storage_setting');
    }
}

/**
 * Get the email notification settings.
 *
 * @return \Illuminate\Database\Eloquent\Collection The email notification settings
 */
if (!function_exists('email_notification_setting')) {
    function email_notification_setting()
    {
        if (in_array('client', user_roles()) || in_array('employee', user_roles())) {
            if (!session()->has('email_notification_setting')) {
                session(['email_notification_setting' => \App\Models\EmailNotificationSetting::all()]);
            }
        }

        if (!session()->has('email_notification_setting')) {
            session(['email_notification_setting' => \App\Models\EmailNotificationSetting::all()]);
        }

        return session('email_notification_setting');
    }
}

/**
 * Generate a URL for an asset in storage.
 *
 * @param string $path The asset path
 * @return string The full URL to the asset
 */
if (!function_exists('asset_url')) {
    function asset_url($path)
    {
        $path = \App\Helper\Files::UPLOAD_FOLDER . '/' . $path;
        $storageUrl = $path;

        if (!Str::startsWith($storageUrl, 'http')) {
            return url($storageUrl);
        }

        return $storageUrl;
    }
}

/**
 * Get the enabled modules for the current user.
 *
 * @return array The array of module names
 */
if (!function_exists('user_modules')) {
    function user_modules()
    {
        $user = user();

        if (!$user) {
            return [];
        }

        if (user()->is_superadmin) {
            return [];
        }

        if (cache()->has('user_modules_' . $user->id)) {
            return cache('user_modules_' . $user->id);
        }

        $module = \App\Models\ModuleSetting::where('is_allowed', 1);

        if (in_array('admin', user_roles())) {
            $module = $module->where('type', 'admin');
        } elseif (in_array('client', user_roles())) {
            $module = $module->where('type', 'client');
        } elseif (in_array('employee', user_roles())) {
            $module = $module->where('type', 'employee');
        }

        $module = $module->where('status', 'active');
        $module->select('module_name');

        $module = $module->get();
        $moduleArray = [];

        foreach ($module->toArray() as $item) {
            $moduleArray[] = array_values($item)[0];
        }

        cache()->put('user_modules_' . $user->id, $moduleArray);

        return $moduleArray;
    }
}

/**
 * Get the enabled Worksuite plugins.
 *
 * @return array The array of plugin names
 */
if (!function_exists('worksuite_plugins')) {
    function worksuite_plugins()
    {
        if (!cache()->has('worksuite_plugins')) {
            $plugins = \Nwidart\Modules\Facades\Module::allEnabled();
            cache(['worksuite_plugins' => array_keys($plugins)]);
        }

        return cache('worksuite_plugins');
    }
}

/**
 * Get the Pusher settings.
 *
 * @return \App\Models\PusherSetting The Pusher settings
 */
if (!function_exists('pusher_settings')) {
    function pusher_settings()
    {
        if (!session()->has('pusher_settings')) {
            session(['pusher_settings' => \App\Models\PusherSetting::first()]);
        }

        return session('pusher_settings');
    }
}

/**
 * Check if the application is seeding data.
 *
 * @return bool True if seeding, false otherwise
 */
if (!function_exists('isSeedingData')) {
    function isSeedingData()
    {
        return config('app.seeding');
    }
}

/**
 * Check if the application is running in console or seeding data.
 *
 * @return bool True if running in console or seeding, false otherwise
 */
if (!function_exists('isRunningInConsoleOrSeeding')) {
    function isRunningInConsoleOrSeeding()
    {
        return app()->runningInConsole() || isSeedingData();
    }
}

/**
 * Generate a URL for an asset, using temporary URLs for S3-compatible storage.
 *
 * @param string $path The asset path
 * @return string The full URL to the asset
 */
if (!function_exists('asset_url_local_s3')) {
    function asset_url_local_s3($path)
    {
        if (in_array(config('filesystems.default'), StorageSetting::S3_COMPATIBLE_STORAGE)) {
            if (\Illuminate\Support\Facades\Cache::has(config('filesystems.default') . '-' . $path)) {
                $temporaryUrl = \Illuminate\Support\Facades\Cache::get(config('filesystems.default') . '-' . $path);
            } else {
                $temporaryUrl = Storage::disk(config('filesystems.default'))->temporaryUrl($path, now()->addMinutes(StorageSetting::HASH_TEMP_FILE_TIME));
                \Illuminate\Support\Facades\Cache::put(config('filesystems.default') . '-' . $path, $temporaryUrl, StorageSetting::HASH_TEMP_FILE_TIME * 60);
            }

            return $temporaryUrl;
        }

        $path = Files::UPLOAD_FOLDER . '/' . $path;
        $storageUrl = $path;

        if (!Str::startsWith($storageUrl, 'http')) {
            return url($storageUrl);
        }

        return $storageUrl;
    }
}

/**
 * Download a file from local or S3-compatible storage.
 *
 * @param mixed $file The file object
 * @param string $path The file path
 * @return \Illuminate\Http\Response The download response or error view
 */
if (!function_exists('download_local_s3')) {
    function download_local_s3($file, $path)
    {
        if (in_array(config('filesystems.default'), StorageSetting::S3_COMPATIBLE_STORAGE)) {
            return Storage::disk(config('filesystems.default'))->download($path, basename($file->filename));
        }

        $path = Files::UPLOAD_FOLDER . '/' . $path;
        $ext = pathinfo($file->filename, PATHINFO_EXTENSION);

        $filename = $file->name ? $file->name . '.' . $ext : $file->filename;
        try {
            return response()->download($path, $filename);
        } catch (\Exception $e) {
            return response()->view('errors.file_not_found', ['message' => $e->getMessage()], 404);
        }
    }
}

/**
 * Get the GDPR settings.
 *
 * @return \App\Models\GdprSetting The GDPR settings
 */
if (!function_exists('gdpr_setting')) {
    function gdpr_setting()
    {
        if (!session()->has('gdpr_setting')) {
            session(['gdpr_setting' => GdprSetting::first()]);
        }

        return session('gdpr_setting');
    }
}

/**
 * Get the social authentication settings.
 *
 * @return \App\Models\SocialAuthSetting The social authentication settings
 */
if (!function_exists('social_auth_setting')) {
    function social_auth_setting()
    {
        if (!cache()->has('social_auth_setting')) {
            cache(['social_auth_setting' => SocialAuthSetting::first()]);
        }

        return cache('social_auth_setting');
    }
}

/**
 * Get the invoice settings.
 *
 * @return \App\Models\InvoiceSetting The invoice settings
 */
if (!function_exists('invoice_setting')) {
    function invoice_setting()
    {
        if (!session()->has('invoice_setting')) {
            $setting = InvoiceSetting::first();
            session(['invoice_setting' => $setting]);
            return $setting;
        }

        return session('invoice_setting');
    }
}

/**
 * Get the global invoice settings.
 *
 * @return \App\Models\SuperAdmin\GlobalInvoiceSetting The global invoice settings
 */
if (!function_exists('global_invoice_setting')) {
    function global_invoice_setting()
    {
        if (!cache()->has('global_invoice_setting')) {
            cache(['global_invoice_setting' => GlobalInvoiceSetting::first()]);
        }

        return cache('global_invoice_setting');
    }
}

/**
 * Get the time log settings.
 *
 * @return \App\Models\LogTimeFor The time log settings
 */
if (!function_exists('time_log_setting')) {
    function time_log_setting()
    {
        if (!session()->has('time_log_setting')) {
            session(['time_log_setting' => LogTimeFor::first()]);
        }

        return session('time_log_setting');
    }
}

/**
 * Check the migration status and run migrations if needed.
 *
 * @return string The migration status
 */
if (!function_exists('check_migrate_status')) {
    function check_migrate_status()
    {
        if (!session()->has('check_migrate_status')) {
            $status = Artisan::call('migrate:check');

            if ($status && !request()->ajax()) {
                Artisan::call('migrate', ['--force' => true, '--schema-path' => 'do not run schema path']);
                Artisan::call('optimize:clear');
            }

            session(['check_migrate_status' => 'Good']);
        }

        return session('check_migrate_status');
    }
}

/**
 * Get all countries.
 *
 * @return \Illuminate\Database\Eloquent\Collection The collection of countries
 */
if (!function_exists('countries')) {
    function countries()
    {
        if (!cache()->has('countries')) {
            cache(['countries' => \App\Models\Country::all()]);
        }

        return cache('countries');
    }
}

/**
 * Check if a specific module is enabled.
 *
 * @param string $moduleName The module name
 * @return bool True if the module is enabled, false otherwise
 */
if (!function_exists('module_enabled')) {
    function module_enabled($moduleName)
    {
        return \Nwidart\Modules\Facades\Module::collections()->has($moduleName);
    }
}

/**
 * Get the currency format settings for a specific currency.
 *
 * @param int|null $currencyId The currency ID (optional)
 * @return \App\Models\Currency The currency format settings
 */
if (!function_exists('currency_format_setting')) {
    function currency_format_setting($currencyId = null)
    {
        if (!session()->has('currency_format_setting' . $currencyId)) {
            $setting = $currencyId == null ? Currency::first() : Currency::where('id', $currencyId)->first();
            session(['currency_format_setting' . $currencyId => $setting]);
        }

        return session('currency_format_setting' . $currencyId);
    }
}

/**
 * Format a currency amount based on settings.
 *
 * @param float $amount The amount to format
 * @param int|null $currencyId The currency ID (optional)
 * @param bool $showSymbol Whether to show the currency symbol
 * @return string The formatted currency string
 */
if (!function_exists('currency_format')) {
    function currency_format($amount, $currencyId = null, $showSymbol = true)
    {
        $formats = currency_format_setting($currencyId);

        if (!$showSymbol) {
            $currency_symbol = '';
        } else {
            $settings = $formats->company ?? Company::find($formats->company_id);
            $currency_symbol = $currencyId == null ? $settings->currency->currency_symbol : $formats->currency_symbol;
        }

        $currency_position = $formats->currency_position;
        $no_of_decimal = !is_null($formats->no_of_decimal) ? $formats->no_of_decimal : '0';
        $thousand_separator = !is_null($formats->thousand_separator) ? $formats->thousand_separator : '';
        $decimal_separator = !is_null($formats->decimal_separator) ? $formats->decimal_separator : '0';

        $amount = number_format($amount, $no_of_decimal, $decimal_separator, $thousand_separator);

        $amount = match ($currency_position) {
            'right' => $amount . $currency_symbol,
            'left_with_space' => $currency_symbol . ' ' . $amount,
            'right_with_space' => $amount . ' ' . $currency_symbol,
            default => $currency_symbol . $amount,
        };

        return $amount;
    }
}

/**
 * Get the attendance settings.
 *
 * @return \App\Models\AttendanceSetting The attendance settings
 */
if (!function_exists('attendance_setting')) {
    function attendance_setting()
    {
        if (!session()->has('attendance_setting')) {
            session(['attendance_setting' => AttendanceSetting::first()]);
        }

        return session('attendance_setting');
    }
}

/**
 * Check if the user has permission to add projects.
 *
 * @return string The permission type
 */
if (!function_exists('add_project_permission')) {
    function add_project_permission()
    {
        if (!session()->has('add_project_permission') && user()) {
            session(['add_project_permission' => user()->permission('add_projects')]);
        }

        return session('add_project_permission');
    }
}

/**
 * Check if the user has permission to add tasks.
 *
 * @return string The permission type
 */
if (!function_exists('add_tasks_permission')) {
    function add_tasks_permission()
    {
        if (!session()->has('add_tasks_permission') && user()) {
            session(['add_tasks_permission' => user()->permission('add_tasks')]);
        }

        return session('add_tasks_permission');
    }
}

/**
 * Check if the user has permission to add clients.
 *
 * @return string The permission type
 */
if (!function_exists('add_clients_permission')) {
    function add_clients_permission()
    {
        if (!session()->has('add_clients_permission') && user()) {
            session(['add_clients_permission' => user()->permission('add_clients')]);
        }

        return session('add_clients_permission');
    }
}

/**
 * Check if the user has permission to add employees.
 *
 * @return string The permission type
 */
if (!function_exists('add_employees_permission')) {
    function add_employees_permission()
    {
        if (!session()->has('add_employees_permission') && user()) {
            session(['add_employees_permission' => user()->permission('add_employees')]);
        }

        return session('add_employees_permission');
    }
}

/**
 * Check if the user has permission to add payments.
 *
 * @return string The permission type
 */
if (!function_exists('add_payments_permission')) {
    function add_payments_permission()
    {
        if (!session()->has('add_payments_permission') && user()) {
            session(['add_payments_permission' => user()->permission('add_payments')]);
        }

        return session('add_payments_permission');
    }
}

/**
 * Check if the user has permission to add tickets.
 *
 * @return string The permission type
 */
if (!function_exists('add_tickets_permission')) {
    function add_tickets_permission()
    {
        if (!session()->has('add_tickets_permission') && user()) {
            session(['add_tickets_permission' => user()->permission('add_tickets')]);
        }

        return session('add_tickets_permission');
    }
}

/**
 * Check if the user has permission to add timelogs.
 *
 * @return string The permission type
 */
if (!function_exists('add_timelogs_permission')) {
    function add_timelogs_permission()
    {
        if (!session()->has('add_timelogs_permission') && user()) {
            session(['add_timelogs_permission' => user()->permission('add_timelogs')]);
        }

        return session('add_timelogs_permission');
    }
}

/**
 * Check if the user has permission to manage active timelogs.
 *
 * @return string The permission type
 */
if (!function_exists('manage_active_timelogs')) {
    function manage_active_timelogs()
    {
        if (!session()->has('manage_active_timelogs') && user()) {
            session(['manage_active_timelogs' => user()->permission('manage_active_timelogs')]);
        }

        return session('manage_active_timelogs');
    }
}

/**
 * Get the Slack settings.
 *
 * @return \App\Models\SlackSetting The Slack settings
 */
if (!function_exists('slack_setting')) {
    function slack_setting()
    {
        if (!session()->has('slack_setting')) {
            session(['slack_setting' => \App\Models\SlackSetting::first()]);
        }

        return session('slack_setting');
    }
}

/**
 * Get the default company address.
 *
 * @return mixed The default address
 */
if (!function_exists('default_address')) {
    function default_address()
    {
        if (!session()->has('default_address')) {
            session(['default_address' => company()->defaultAddress]);
        }

        return session('default_address');
    }
}

/**
 * Abort with a 403 error if the condition is true.
 *
 * @param bool $condition The condition to check
 */
if (!function_exists('abort_403')) {
    function abort_403($condition)
    {
        abort_if($condition, 403, __('messages.permissionDenied'));
    }
}

/**
 * Get the sidebar permissions for the current user.
 *
 * @return array The array of permission names and types
 */
if (!function_exists('sidebar_user_perms')) {
    function sidebar_user_perms()
    {
        if (!cache()->has('sidebar_user_perms_' . user()->id)) {
            $sidebarPermissionsArray = [
                'view_clients', 'view_lead', 'view_employees', 'view_leave', 'view_attendance', 'view_holiday',
                'view_contract', 'view_projects', 'view_tasks', 'view_timelogs', 'view_estimates', 'view_invoices',
                'view_payments', 'view_expenses', 'view_product', 'view_order', 'view_tickets', 'view_events',
                'view_notice', 'view_task_report', 'view_time_log_report', 'view_finance_report',
                'view_income_expense_report', 'view_leave_report', 'view_lead_proposals', 'view_attendance_report',
                'manage_company_setting', 'add_employees', 'view_knowledgebase', 'view_shift_roster',
                'view_designation', 'view_department', 'view_overview_dashboard', 'view_project_dashboard',
                'view_client_dashboard', 'view_hr_dashboard', 'view_ticket_dashboard', 'view_finance_dashboard',
                'view_expense_report', 'view_client_note', 'view_bankaccount', 'view_appreciation',
                'manage_award', 'view_lead_report', 'view_sales_report', 'view_deals',
            ];

            $sidebarPermissions = Permission::whereIn('name', $sidebarPermissionsArray)->select('id', 'name')->orderBy('id', 'asc')->get();

            $sidebarPermissionsId = $sidebarPermissions->pluck('id')->toArray();

            $sidebarUserPermissionType = UserPermission::where('user_id', user()->id)
                ->whereIn('permission_id', $sidebarPermissionsId)
                ->join('permissions', 'permissions.id', '=', 'user_permissions.permission_id')
                ->select('user_permissions.permission_type_id', 'permissions.name', 'permissions.id')
                ->groupBy(['user_id', 'permission_id', 'permission_type_id'])
                ->get()
                ->keyBy('name');

            $sidebarUserPermissions = array_combine($sidebarUserPermissionType->pluck('name')->toArray(), $sidebarUserPermissionType->pluck('permission_type_id')->toArray());

            $unassignedPermissions = array_diff($sidebarPermissionsId, $sidebarUserPermissionType->pluck('id')->toArray());

            $filteredPermissions = $sidebarPermissions->filter(function ($item) use ($unassignedPermissions) {
                return in_array($item->id, $unassignedPermissions);
            });

            foreach ($filteredPermissions as $item) {
                $sidebarUserPermissions[$item->name] = 5;
            }

            cache(['sidebar_user_perms_' . user()->id => $sidebarUserPermissions]);
        }

        return cache('sidebar_user_perms_' . user()->id);
    }
}

/**
 * Get the sidebar permissions for the superadmin user.
 *
 * @return array The array of superadmin permission names and types
 */
if (!function_exists('sidebar_superadmin_perms')) {
    function sidebar_superadmin_perms()
    {
        session()->forget('sidebar_superadmin_perms');

        if (!session()->has('sidebar_superadmin_perms')) {
            $sidebarPermissionsArray = [
                'view_packages', 'view_companies', 'manage_billing', 'view_request', 'view_admin_faq',
                'view_superadmin', 'view_superadmin_ticket', 'manage_superadmin_front_settings',
            ];

            $superadminSidebarPermissions = Permission::whereIn('name', $sidebarPermissionsArray)
                ->whereHas('module', function ($query) {
                    $query->withoutGlobalScopes()->where('is_superadmin', '1');
                })->orderBy('id', 'asc')->get();

            $superadminSidebarPermissionsId = $superadminSidebarPermissions->pluck('id')->toArray();

            $sidebarSuperadminPermissionType = UserPermission::where('user_id', user()->id)
                ->whereIn('permission_id', $superadminSidebarPermissionsId)
                ->join('permissions', 'permissions.id', '=', 'user_permissions.permission_id')
                ->orderBy('user_permissions.id')
                ->select('user_permissions.permission_type_id', 'permissions.name', 'permissions.id')
                ->groupBy(['user_id', 'permission_id', 'permission_type_id'])
                ->get()
                ->keyBy('name');

            $sidebarSuperadminPermissions = array_combine($sidebarSuperadminPermissionType->pluck('name')->toArray(), $sidebarSuperadminPermissionType->pluck('permission_type_id')->toArray());

            $unassignedPermissions = array_diff($superadminSidebarPermissionsId, $sidebarSuperadminPermissionType->pluck('id')->toArray());

            $filteredPermissions = $superadminSidebarPermissions->filter(function ($item) use ($unassignedPermissions) {
                return in_array($item->id, $unassignedPermissions);
            });

            foreach ($filteredPermissions as $item) {
                $sidebarSuperadminPermissions[$item->name] = 5;
            }

            session(['sidebar_superadmin_perms' => $sidebarSuperadminPermissions]);
        }

        return session('sidebar_superadmin_perms');
    }
}

/**
 * Capitalize the first character of a string, supporting multibyte encodings.
 *
 * @param string $string The input string
 * @param string $encoding The character encoding (default: utf8)
 * @return string The string with the first character capitalized
 */
if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string, $encoding = 'utf8')
    {
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, null, $encoding);

        return mb_strtoupper($firstChar, $encoding) . $then;
    }
}

/**
 * Capitalize the first character of each word in a string, supporting multibyte encodings.
 *
 * @param string $string The input string
 * @param string $encoding The character encoding (default: utf8)
 * @return string The string with each word capitalized
 */
if (!function_exists('mb_ucwords')) {
    function mb_ucwords($string, $encoding = 'utf8')
    {
        return mb_convert_case($string, MB_CASE_TITLE, $encoding);
    }
}

/**
 * Convert minutes to a human-readable hour format.
 *
 * @param int $totalMinutes The total minutes
 * @return string The formatted time
 */
if (!function_exists('minute_to_hour')) {
    function minute_to_hour($totalMinutes)
    {
        return \Carbon\CarbonInterval::formatHuman($totalMinutes);
    }
}

/**
 * Check if the user can upload files based on storage limits.
 *
 * @param int $size The size of the file to upload (in MB)
 * @return bool True if upload is allowed, false otherwise
 */
if (!function_exists('can_upload')) {
    function can_upload($size = 0)
    {
        if (!session()->has('client_company')) {
            session()->forget(['company_setting', 'company']);
        }

        if (company()->package->max_storage_size == -1) {
            return true;
        }

        $totalSpace = (company()->package->storage_unit == 'mb') ? company()->package->max_storage_size : company()->package->max_storage_size * 1024;

        $fileStorage = \App\Models\FileStorage::all();
        $usedSpace = $fileStorage->count() > 0 ? round($fileStorage->sum('size') / (1000 * 1024), 4) : 0;

        $remainingSpace = $totalSpace - $usedSpace;

        if ($usedSpace > $totalSpace || $size > $remainingSpace) {
            return false;
        }

        return true;
    }
}

/**
 * Check if the application is Worksuite SaaS.
 *
 * @return bool True if Worksuite SaaS, false otherwise
 */
if (!function_exists('isWorksuiteSaas')) {
    function isWorksuiteSaas()
    {
        return strtolower(config('app.app_name')) === 'worksuite-saas';
    }
}

/**
 * Check if the application is Worksuite.
 *
 * @return bool True if Worksuite, false otherwise
 */
if (!function_exists('isWorksuite')) {
    function isWorksuite()
    {
        return strtolower(config('app.app_name')) === 'worksuite';
    }
}

/**
 * Check if IDs should be shown (true for Worksuite).
 *
 * @return bool True if Worksuite, false otherwise
 */
if (!function_exists('showId')) {
    function showId()
    {
        return isWorksuite();
    }
}

/**
 * Get a domain-specific URL for a company.
 *
 * @param string $url The original URL
 * @param \App\Models\Company|null $company The company (optional)
 * @return string The modified URL
 */
if (!function_exists('getDomainSpecificUrl')) {
    function getDomainSpecificUrl($url, $company = null)
    {
        if (!module_enabled('Subdomain')) {
            return $url;
        }

        config(['app.url' => config('app.main_app_url')]);

        if ($company) {
            $companyUrl = (config('app.redirect_https') ? 'https' : 'http') . '://' . $company->sub_domain;

            config(['app.url' => $companyUrl]);

            if (Str::contains($url, $company->sub_domain)) {
                return $url;
            }

            $url = str_replace(request()->getHost(), $company->sub_domain, $url);
            $url = str_replace('www.', '', $url);

            if (!config('app.redirect_https')) {
                return str_replace('https', 'http', $url);
            }

            return $url;
        }

        return str_replace('login', 'super-admin-login', $url);
    }
}

/**
 * Get the current company.
 *
 * @return \App\Models\Company|bool The company object or false if not found
 */
if (!function_exists('company')) {
    function company()
    {
        if (session()->has('company')) {
            return session('company');
        }

        if (user()) {
            if (user()->company_id) {
                $company = Company::find(user()->company_id);
                session(['company' => $company]);
                return $company;
            }

            return session('company');
        }

        return false;
    }
}

/**
 * Get the company or global settings based on user context.
 *
 * @return \App\Models\Company|\App\Models\GlobalSetting The company or global settings
 */
if (!function_exists('companyOrGlobalSetting')) {
    function companyOrGlobalSetting()
    {
        if (user()) {
            if (user()->company_id) {
                return company();
            }
        }

        return global_setting();
    }
}

/**
 * Trim HTML editor content by removing trailing <p><br></p>.
 *
 * @param string $text The input text
 * @return string The trimmed text
 */
if (!function_exists('trim_editor')) {
    function trim_editor($text)
    {
        $search = '/' . preg_quote('<p><br></p>', '/') . '/';
        return preg_replace($search, '', trim($text), 1);
    }
}

/**
 * Get the QuickBooks settings.
 *
 * @return \App\Models\QuickBooksSetting The QuickBooks settings
 */
if (!function_exists('quickbooks_setting')) {
    function quickbooks_setting()
    {
        if (!session()->has('quickbooks_setting')) {
            $qbSetting = QuickBooksSetting::first();
            session(['quickbooks_setting' => $qbSetting]);
            return $qbSetting;
        }

        return session('quickbooks_setting');
    }
}

/**
 * Get the role IDs of the current logged-in user.
 *
 * @return array|null The array of role IDs or null if no user
 */
if (!function_exists('user_role_ids')) {
    function user_role_ids()
    {
        if (session()->has('user_role_ids')) {
            return session('user_role_ids');
        }

        return null;
    }
}

/**
 * Check if the user can export data tables.
 *
 * @return bool True if export is allowed, false otherwise
 */
if (!function_exists('canDataTableExport')) {
    function canDataTableExport()
    {
        return in_array('admin', user_roles()) || (company()->employee_can_export_data && in_array('employee', user_roles()));
    }
}

/**
 * Strip HTML tags from text, allowing specific tags for PDF rendering.
 *
 * @param string $text The input text
 * @return string The text with allowed tags
 */
if (!function_exists('pdfStripTags')) {
    function pdfStripTags($text)
    {
        return strip_tags($text, [
            'p', 'b', 'strong', 'a', 'ul', 'li', 'ol', 'i', 'u', 'blockquote', 'img', 'h1', 'h2', 'h3', 'h4', 'h5',
        ]);
    }
}

/**
 * Convert a date from company format to Y-m-d format.
 *
 * @param string $date The date in company format
 * @return string The date in Y-m-d format
 */
if (!function_exists('companyToYmd')) {
    function companyToYmd($date)
    {
        return Carbon::createFromFormat(company()->date_format, $date)->format('Y-m-d');
    }
}

/**
 * Convert a date from company format to a date string.
 *
 * @param string $date The date in company format
 * @return string The date in Y-m-d format
 */
if (!function_exists('companyToDateString')) {
    function companyToDateString($date)
    {
        return Carbon::createFromFormat(company()->date_format, $date)->toDateString();
    }
}

/**
 * Get the custom link settings.
 *
 * @return \Illuminate\Database\Eloquent\Collection The custom link settings
 */
if (!function_exists('custom_link_setting')) {
    function custom_link_setting()
    {
        if (!session()->has('custom_link_setting')) {
            session(['custom_link_setting' => CustomLinkSetting::all()]);
        }

        return session('custom_link_setting');
    }
}

/**
 * Check if the language is right-to-left (RTL).
 *
 * @param string|null $class The CSS class to return if RTL (optional)
 * @return bool|string The RTL status or class if specified
 */
if (!function_exists('isRtl')) {
    function isRtl($class = null)
    {
        if (!session()->has('isRtl')) {
            $rtl = false;

            if (user()) {
                $locale = user()->locale;
            } else {
                $locale = global_setting()->locale;
            }

            if ($locale) {
                $userLanguage = LanguageSetting::where('language_code', $locale)->first();

                if ($userLanguage) {
                    $rtl = $userLanguage->is_rtl;
                }
            }

            session(['isRtl' => $rtl]);
        }

        $isRtl = session('isRtl');

        return is_null($class) ? $isRtl : ($isRtl ? $class : false);
    }
}

/**
 * Get the global currency format settings.
 *
 * @param int|null $currencyId The currency ID (optional)
 * @return \App\Models\SuperAdmin\GlobalCurrency The global currency format settings
 */
if (!function_exists('global_currency_format_setting')) {
    function global_currency_format_setting($currencyId = null)
    {
        if (!cache()->has('global_currency_format_setting' . $currencyId)) {
            $setting = $currencyId == null ? GlobalCurrency::first() : GlobalCurrency::withTrashed()->where('id', $currencyId)->first();
            cache(['global_currency_format_setting' . $currencyId => $setting]);
        }

        return cache('global_currency_format_setting' . $currencyId);
    }
}

/**
 * Format a global currency amount based on settings.
 *
 * @param float $amount The amount to format
 * @param int|null $currencyId The currency ID (optional)
 * @param bool $showSymbol Whether to show the currency symbol
 * @return string The formatted currency string
 */
if (!function_exists('global_currency_format')) {
    function global_currency_format($amount, $currencyId = null, $showSymbol = true)
    {
        $globalformat = global_currency_format_setting($currencyId);
        $settings = companyOrGlobalSetting();

        if ($showSymbol == false) {
            $currency_symbol = '';
        } else {
            $currency_symbol = $currencyId == null && $settings->currency ? $settings->currency->currency_symbol : $globalformat->currency_symbol;
        }

        $currency_position = $globalformat->currency_position;
        $no_of_decimal = !is_null($globalformat->no_of_decimal) ? $globalformat->no_of_decimal : '0';
        $thousand_separator = !is_null($globalformat->thousand_separator) ? $globalformat->thousand_separator : '';
        $decimal_separator = !is_null($globalformat->decimal_separator) ? $globalformat->decimal_separator : '0';

        $amount = number_format($amount, $no_of_decimal, $decimal_separator, $thousand_separator);

        $amount = match ($currency_position) {
            'right' => $amount . $currency_symbol,
            'left_with_space' => $currency_symbol . ' ' . $amount,
            'right_with_space' => $amount . ' ' . $currency_symbol,
            default => $currency_symbol . $amount,
        };

        return $amount;
    }
}

/**
 * Get the companies associated with a user.
 *
 * @param \App\Models\User $user The user object
 * @return \Illuminate\Database\Eloquent\Collection The collection of user companies
 */
if (!function_exists('user_companies')) {
    function user_companies($user)
    {
        if (!session()->has('user_companies')) {
            $userCompanies = User::withoutGlobalScope(CompanyScope::class)
                ->where('email', $user->email)
                ->where('login', 'enable')
                ->whereHas('approvedCompany')
                ->with('company')
                ->withOut('clientDetails', 'role', 'employeeDetail')
                ->select('id', 'company_id', 'status')
                ->get();

            session(['user_companies' => $userCompanies]);
            return $userCompanies;
        }

        return session('user_companies');
    }
}

/**
 * Flush company-specific session data.
 */
if (!function_exists('flushCompanySpecificSessions')) {
    function flushCompanySpecificSessions()
    {
        session()->forget([
            'user_roles', 'admin_theme', 'employee_theme', 'client_theme', 'message_setting',
            'email_notification_setting', 'invoice_setting', 'time_log_setting', 'currency_format_setting',
            'attendance_setting', 'add_project_permission', 'add_tasks_permission', 'add_clients_permission',
            'add_employees_permission', 'add_payments_permission', 'add_tickets_permission',
            'add_timelogs_permission', 'manage_active_timelogs', 'slack_setting', 'default_address',
            'sidebar_user_perms', 'quickbooks_setting', 'user_permissions',
        ]);
    }
}

/**
 * Check if the company's package is valid based on employee count.
 *
 * @param int|null $companyId The company ID
 * @return bool True if the package is valid, false otherwise
 */
if (!function_exists('checkCompanyPackageIsValid')) {
    function checkCompanyPackageIsValid($companyId)
    {
        if (is_null($companyId)) {
            return true;
        }

        return cache()->rememberForever('company_' . $companyId . '_valid_package', function () use ($companyId) {
            $company = Company::with('package')->withCount('employees')->find($companyId);
            return $company->employees_count <= $company->package->max_employees;
        });
    }
}

/**
 * Check if the company can add more employees based on package limits.
 *
 * @param int|null $companyId The company ID
 * @return bool True if more employees can be added, false otherwise
 */
if (!function_exists('checkCompanyCanAddMoreEmployees')) {
    function checkCompanyCanAddMoreEmployees($companyId)
    {
        if (is_null($companyId)) {
            return true;
        }

        return cache()->rememberForever('company_' . $companyId . '_can_add_more_employees', function () use ($companyId) {
            $company = Company::with('package')->withCount('employees')->find($companyId);
            return $company->employees_count < $company->package->max_employees;
        });
    }
}

/**
 * Check if the company is inactive.
 *
 * @param int|null $companyId The company ID
 * @return bool True if the company is inactive, false otherwise
 */
if (!function_exists('checkActiveCompany')) {
    function checkActiveCompany($companyId)
    {
        if (is_null($companyId)) {
            return true;
        }

        return cache()->rememberForever('user_' . $companyId . '_is_active', function () use ($companyId) {
            return Company::where('status', 'inactive')->where('id', $companyId)->exists();
        });
    }
}

/**
 * Clear the cache for company package validity and employee limits.
 *
 * @param int|null $companyId The company ID
 * @return bool True if cache is cleared or company ID is null
 */
if (!function_exists('clearCompanyValidPackageCache')) {
    function clearCompanyValidPackageCache($companyId)
    {
        if (is_null($companyId)) {
            return true;
        }

        cache()->forget('company_' . $companyId . '_valid_package');
        cache()->forget('company_' . $companyId . '_can_add_more_employees');
    }
}