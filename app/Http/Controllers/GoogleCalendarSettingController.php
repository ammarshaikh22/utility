<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\GoogleCalenderSetting\StoreGoogleCalender;
use App\Models\Company;
use App\Models\GlobalSetting;
use App\Models\GoogleCalendarModule;
use App\Traits\GoogleOAuth;
use Illuminate\Support\Facades\Artisan;

class GoogleCalendarSettingController extends AccountBaseController
{
    use GoogleOAuth;

    public function __construct()
    {
        $this->setGoogleoAuthConfig();
        parent::__construct();

        $this->pageTitle = __('app.menu.googleCalendarSetting');
        $this->activeSettingMenu = 'google_calendar_settings';

        $this->middleware(function ($request, $next) {
            abort_403(
                GlobalSetting::validateSuperAdmin('manage_superadmin_calendar_settings') &&
                (user()->permission('manage_google_calendar_setting') !== 'all')
            );
            return $next($request);
        });
    }

    /**
     * Display Google Calendar settings page.
     */
    public function index()
    {
        $this->globalSetting = global_setting();

        abort_403(!user()->is_superadmin && $this->globalSetting->google_calendar_status === 'inactive');

        $this->companyOrGlobalSetting = companyOrGlobalSetting();
        $this->setting = company();
        $this->module = GoogleCalendarModule::first();

        return view('google-calendar-settings.index', $this->data);
    }

    /**
     * Store or update Google Calendar configuration.
     */
    public function store(StoreGoogleCalender $request)
    {
        if (user()->is_superadmin) {
            // Global (super admin) Google Calendar settings
            $googleCalendarSetting = global_setting();
            $googleCalendarSetting->google_calendar_status = $request->status ? 'active' : 'inactive';
            $googleCalendarSetting->google_client_id = $request->google_client_id;
            $googleCalendarSetting->google_client_secret = $request->google_client_secret;
            $googleCalendarSetting->save();

            if (!$request->status) {
                Company::query()->update(['google_calendar_status' => 'inactive']);

                GoogleCalendarModule::query()->update(
                    array_fill_keys([
                        'lead_status',
                        'leave_status',
                        'invoice_status',
                        'contract_status',
                        'task_status',
                        'event_status',
                        'holiday_status',
                    ], 0)
                );
            }
        } else {
            // Company-level Google Calendar settings
            $googleCalendarSetting = company();
            $googleCalendarSetting->google_calendar_status = $request->status ? 'active' : 'inactive';
            $googleCalendarSetting->save();

            // Update Google Calendar module notifications
            $module = GoogleCalendarModule::first();
            $module->lead_status = $request->lead_status ?? 0;
            $module->leave_status = $request->leave_status ?? 0;
            $module->invoice_status = $request->invoice_status ?? 0;
            $module->contract_status = $request->contract_status ?? 0;
            $module->task_status = $request->task_status ?? 0;
            $module->event_status = $request->event_status ?? 0;
            $module->holiday_status = $request->holiday_status ?? 0;
            $module->save();
        }

        // Clear session & cache
        session()->forget(['companyOrGlobalSetting', 'user.company', 'company']);
        cache()->forget('global_setting');

        // Refresh Laravel caches
        if ($request->cache) {
            Artisan::call('optimize');
            Artisan::call('route:clear');
        } else {
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
        }

        return Reply::success(__('messages.updateSuccess'));
    }
}
