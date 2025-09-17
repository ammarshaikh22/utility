<?php

namespace App\Actions\Fortify;

use App\Helper\Reply;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\EmployeeShiftSchedule;
use App\Models\GlobalSetting;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\CompanyAddress;
use App\Models\Company;
use App\Models\User;
use App\Scopes\ActiveScope;
use Carbon\Carbon;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;
use Illuminate\Support\Facades\DB;

class AttemptToAuthenticate
{

    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * The login rate limiter instance.
     *
     * @var \Laravel\Fortify\LoginRateLimiter
     */
    protected $limiter;

    /**
     * Create a new controller instance.
     *
     * @param \Illuminate\Contracts\Auth\StatefulGuard $guard
     * @param \Laravel\Fortify\LoginRateLimiter $limiter
     * @return void
     */
    public function __construct(StatefulGuard $guard, LoginRateLimiter $limiter)
    {
        $this->guard = $guard;
        $this->limiter = $limiter;
    }

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param callable $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        // Retrieve global settings for the application
        $globalSetting = GlobalSetting::first();

        // Extract the intended URL from session and parse the company token from the path
        $url = session('url.intended');
        $path = parse_url($url, PHP_URL_PATH);
        $token = basename($path);
        $company = Company::where('hash', $token)->first();

        // Query for the authenticating user by email, optionally filtering by company
        $authUser = User::withoutGlobalScope(ActiveScope::class)->where('email', $request->email);

        if ($company) {
            $authUser->where('company_id', $company->id);
        }
        $authUser = $authUser->first();

        // Validate that the user belongs to the correct company if a company is specified
        // Check if the user exists and belongs to the correct company
        if ($company && $company->id !== $authUser->company_id) {
            $this->checkCompany();
        }


        // If the user has a company, check attendance settings and auto clock-in conditions
        if($authUser->company){
            $attendanceSetting = $authUser->company->attendanceSetting;
            $checkAutoClockinConditions = $this->checkAutoClockinConditions($authUser);

            // Perform auto clock-in if enabled and conditions are met, including optional radius check
            if ($attendanceSetting->auto_clock_in == 'yes' && $checkAutoClockinConditions) {
                if (($attendanceSetting->radius_check == 'yes' && $this->isInRadius($request, $attendanceSetting)) || $attendanceSetting->radius_check == 'no') {
                    $this->storeClockIn($request, $authUser->id);
                }
            }
        }

        // Validate Google reCAPTCHA if enabled in global settings
        if ($globalSetting->google_recaptcha_status == 'active') {
            $gRecaptchaResponseInput = 'g-recaptcha-response';
            $gRecaptchaResponse = $request->{$gRecaptchaResponseInput};

            $gRecaptchaResponse = $globalSetting->google_recaptcha_v2_status == 'active' ? $gRecaptchaResponse : $request->g_recaptcha;

            if (is_null($gRecaptchaResponse)) {
                return $this->googleRecaptchaMessage();
            }

            $validateRecaptcha = GlobalSetting::validateGoogleRecaptcha($gRecaptchaResponse);

            if (!$validateRecaptcha) {
                return $this->googleRecaptchaMessage();
            }
        }

        // Use custom authentication callback if defined
        if (Fortify::$authenticateUsingCallback) {
            return $this->handleUsingCustomCallback($request, $next);
        }

        // Attempt standard authentication using the guard
        if ($this->guard->attempt(
            $request->only(Fortify::username(), 'password'),
            $request->filled('remember'))
        ) {
            return $next($request);
        }

        // Throw exception on authentication failure
        $this->throwFailedAuthenticationException($request);
    }

    // Helper method to check if the user's current location is within the specified radius of the company address
    private function isInRadius($request, $attendanceSetting)
    {
        $radius = $attendanceSetting->radius;
        $currentLatitude = $request->current_latitude;
        $currentLongitude = $request->current_longitude;

        // Retrieve user and their associated company address
        $user = User::with('employeeDetail')->where('email', $request->email)->first();

        if ($user->employeeDetail && $user->employeeDetail->company_address_id) {
            $location = CompanyAddress::findOrFail($user->employeeDetail->company_address_id);
        } else {
            $location = CompanyAddress::where('is_default', 1)->where('company_id',$user->company_id)->first();
        }

        // Convert coordinates to radians and calculate distance using haversine formula
        $latFrom = deg2rad($location->latitude);
        $latTo = deg2rad($currentLatitude);

        $lonFrom = deg2rad($location->longitude);
        $lonTo = deg2rad($currentLongitude);

        $theta = $lonFrom - $lonTo;

        $dist = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($theta);
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $distance = $dist * 60 * 1.1515 * 1609.344;

        // Return true if the calculated distance is within the allowed radius
        return $distance <= $radius;
    }

    // Throws a validation exception if the user is not associated with the correct company
    protected function checkCompany()
    {
        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.loginWrongCompany')],
        ]);
    }

    // Checks various conditions to determine if auto clock-in is allowed (e.g., time, shifts, leaves, holidays)
    public function checkAutoClockinConditions($authUser)
    {

        $globalSetting = GlobalSetting::first();
        $showClockIn = $authUser->company->attendanceSetting;

        // Determine applicable attendance settings based on shift
        $attendanceSettings = $this->attendanceShift($showClockIn, $authUser->id, $authUser->company);

        // Calculate office start and end times for the current day in UTC
        $startTimestamp = now()->format('Y-m-d') . ' ' . $attendanceSettings->office_start_time;
        $endTimestamp = now()->format('Y-m-d') . ' ' . $attendanceSettings->office_end_time;
        $officeStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTimestamp, $globalSetting->timezone);
        $officeEndTime = Carbon::createFromFormat('Y-m-d H:i:s', $endTimestamp, $globalSetting->timezone);
        $officeStartTime = $officeStartTime->setTimezone('UTC');
        $officeEndTime = $officeEndTime->setTimezone('UTC');

        // Adjust end time if it crosses midnight
        if ($officeStartTime->gt($officeEndTime)) {
            $officeEndTime->addDay();
        }

        // Initialize flag for login restriction based on time
        $cannotLogin = false;

        // Check if current time is outside office hours for strict shifts
        if (is_null($attendanceSettings->early_clock_in) && !now()->between($officeStartTime, $officeEndTime) && $attendanceSettings->shift_type == 'strict') {
            $cannotLogin = true;
        }
        elseif ($attendanceSettings->shift_type == 'strict') {
            // For shifts with early clock-in allowance, check if within allowed window
            $earlyClockIn = now($globalSetting->timezone)->addMinutes($attendanceSettings->early_clock_in);
            $earlyClockIn = $earlyClockIn->setTimezone('UTC');

            if($earlyClockIn->gte($officeStartTime)){
                $cannotLogin = false;
            }
            else {
                $cannotLogin = true;
            }
        }

        // Override restriction if within previous day's office hours
        if ($cannotLogin && now()->betweenIncluded($officeStartTime->copy()->subDay(), $officeEndTime->copy()->subDay())) {
            $cannotLogin = false;
        }

        // Getting Current Clock-in if exist
        // Check for current day's clock-in
        $currentClockIn = Attendance::where(DB::raw('DATE(clock_in_time)'), now()->format('Y-m-d'))
            ->where('user_id', $authUser->id)
            ->first();
        

        $currentDate = now($globalSetting->timezone)->format('Y-m-d');

        // Check if user has an approved full-day leave today
        $checkTodayLeave = Leave::where('status', 'approved')
            ->where('leave_date', now($globalSetting->timezone)->toDateString())
            ->where('user_id', $authUser->id)
            ->where('duration', '<>', 'half day')
            ->first();

        $employeeClockInOut = $showClockIn->employee_clock_in_out == 'yes';

        $showClockInButton = $showClockIn->show_clock_in_button == 'yes';

        // Check Holiday by date
        $checkTodayHoliday = Holiday::where('date', $currentDate)->first();

        // Return true if conditions allow clock-in: no existing clock-in, no leave, no holiday, and time is appropriate
        if (!$cannotLogin && $currentClockIn == null && $checkTodayLeave == null && is_null($checkTodayHoliday)) {
            return true;
        }

        // Allow clock-in if button is shown and employee clock-in is enabled, even if time-restricted
        if ($showClockInButton && $employeeClockInOut && $cannotLogin && $currentClockIn == null) {
            return true;
        }

        return false;
    }

    // Stores a new clock-in attendance record if conditions are met
    public function storeClockIn($request, $authUser)
    {
        $now = now();
        // Retrieve user with employee details and company settings
        $company = User::with('employeeDetails')->where('id', $authUser)->first();
        $authUserCompany = User::withoutGlobalScope(ActiveScope::class)->where('id', $authUser)->first();
        $showClockIn = AttendanceSetting::where('company_id', $company->company_id)->first();
        $globalSetting = GlobalSetting::first();
        $attendanceSettings = $this->attendanceShift($showClockIn, $authUser, $authUserCompany->company);
        $attendanceUser = User::find($authUser);

        // Calculate office start and end times for the current day in UTC
        $startTimestamp = now()->format('Y-m-d') . ' ' . $attendanceSettings->office_start_time;
        $endTimestamp = now()->format('Y-m-d') . ' ' . $attendanceSettings->office_end_time;
        $officeStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTimestamp, $globalSetting->timezone);
        $officeEndTime = Carbon::createFromFormat('Y-m-d H:i:s', $endTimestamp, $globalSetting->timezone);
        $officeStartTime = $officeStartTime->setTimezone('UTC');
        $officeEndTime = $officeEndTime->setTimezone('UTC');

        // Adjust end time if it crosses midnight
        if ($officeStartTime->gt($officeEndTime)) {
            $officeEndTime->addDay();
        }

        $cannotLogin = false;
        // Count existing clock-ins within the shift window
        $clockInCount = Attendance::getTotalUserClockInWithTime($officeStartTime, $officeEndTime, $authUser);

        // Determine if clock-in is restricted based on time and shift type
        if (is_null($attendanceSettings->early_clock_in) && !now()->between($officeStartTime, $officeEndTime) && $showClockIn->show_clock_in_button == 'no' && $attendanceSettings->shift_type == 'strict') {
            $cannotLogin = true;
        }
        elseif ($attendanceSettings->shift_type == 'strict') {
            $earlyClockIn = now($globalSetting->timezone)->addMinutes($attendanceSettings->early_clock_in);
            $earlyClockIn = $earlyClockIn->setTimezone('UTC');

            if($earlyClockIn->gte($officeStartTime)){
                $cannotLogin = false;
            }
            else {
                $cannotLogin = true;
            }
        }

        // Override restriction for auto clock-in
        ($showClockIn->auto_clock_in == 'yes') ? $cannotLogin = false : $cannotLogin = true;

        // Check if within previous day's shift and adjust count accordingly
        if ($cannotLogin && now()->betweenIncluded($officeStartTime->copy()->subDay(), $officeEndTime->copy()->subDay()) && $attendanceSettings->shift_type == 'strict') {
            $cannotLogin = false;
            $clockInCount = Attendance::getTotalUserClockInWithTime($officeStartTime->copy()->subDay(), $officeEndTime->copy()->subDay(), $authUser);

        } else {
            $clockInCount = Attendance::whereDate('clock_in_time', $officeStartTime->copy()->toDateString())
            ->where('user_id', $authUser)
            ->count();
        }

        // Abort if clock-in is not allowed due to time restrictions
        if ($cannotLogin) {
            abort(403, __('messages.permissionDenied'));
        }

        // Check user by ip
        // Validate IP address if IP checking is enabled
        if (attendance_setting()->ip_check == 'yes') {
            $ips = (array)json_decode(attendance_setting()->ip_address);

            if (!in_array($request->ip(), $ips)) {
                return Reply::error(__('messages.notAnAuthorisedDevice'));
            }
        }

        // Check maximum attendance in a day
        // Proceed to create attendance if under daily limit
        if ($clockInCount < $attendanceSettings->clockin_in_day && !$cannotLogin) {

            // Set TimeZone And Convert into timestamp
            $currentTimestamp = $now->setTimezone('UTC');
            $currentTimestamp = $currentTimestamp->timestamp;;

            // Set TimeZone And Convert into timestamp in halfday time
            if ($attendanceSettings->halfday_mark_time) {
                $halfDayTimestamp = $now->format('Y-m-d') . ' ' . $attendanceSettings->halfday_mark_time;
                $halfDayTimestamp = Carbon::createFromFormat('Y-m-d H:i:s', $halfDayTimestamp, $globalSetting->timezone);
                $halfDayTimestamp = $halfDayTimestamp->setTimezone('UTC');
                $halfDayTimestamp = $halfDayTimestamp->timestamp;
            }


            $timestamp = $now->format('Y-m-d') . ' ' . $attendanceSettings->office_start_time;
            $officeStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, $globalSetting->timezone);
            $officeStartTime = $officeStartTime->setTimezone('UTC');


            $lateTime = $officeStartTime->addMinutes($attendanceSettings->late_mark_duration);

            // Check for existing attendance today
            $checkTodayAttendance = Attendance::where('user_id', $authUser)
                ->where(DB::raw('DATE(attendances.clock_in_time)'), '=', $now->format('Y-m-d'))->first();


            // Get default company address if no employee-specific one
            $defaultAddress = CompanyAddress::where('is_default', 1)->where('company_id', $attendanceUser->company_id)->first();

            // Create new attendance record
            $attendance = new Attendance();
            $attendance->user_id = $authUser;
            $attendance->clock_in_time = $now;
            $attendance->clock_in_ip = request()->ip();

            $attendance->working_from = $showClockIn->auto_clock_in_location;
            $attendance->work_from_type = $showClockIn->auto_clock_in_location;
            $attendance->location_id = $company->employeeDetails->company_address_id ?? $defaultAddress->id;

            // Mark as late if clock-in is after late threshold and no prior attendance
            if ($now->gt($lateTime) && is_null($checkTodayAttendance) && $attendanceSettings->shift_type == 'strict') {
                $attendance->late = 'yes';
            }

            $attendance->half_day = 'no'; // default halfday

            // Check day's first record and half day time
            // Mark as half-day if clock-in after half-day threshold and conditions met
            if (
                !is_null($attendanceSettings->halfday_mark_time)
                && is_null($checkTodayAttendance)
                && isset($halfDayTimestamp)
                && ($currentTimestamp > $halfDayTimestamp)
                && ($showClockIn->show_clock_in_button == 'no') // DO NOT allow half day when allowed outside hours clock-in
                && $attendanceSettings->shift_type == 'strict'
            ) {
                $attendance->half_day = 'yes';
            }

            // Store location coordinates if provided
            $currentLatitude = $request->currentLatitude;
            $currentLongitude = $request->currentLongitude;

            if ($currentLatitude != '' && $currentLongitude != '') {
                $attendance->latitude = $currentLatitude;
                $attendance->longitude = $currentLongitude;
            }

            $attendance->employee_shift_id = $attendanceSettings->id;

            // Set shift start time
            $attendance->shift_start_time = $attendance->clock_in_time->toDateString() . ' ' . $attendanceSettings->office_start_time;

            // Set shift end time, adjusting for overnight shifts
            if (Carbon::parse($attendanceSettings->office_start_time)->gt(Carbon::parse($attendanceSettings->office_end_time))) {
                $attendance->shift_end_time = $attendance->clock_in_time->addDay()->toDateString() . ' ' . $attendanceSettings->office_end_time;

            }
            else {
                $attendance->shift_end_time = $attendance->clock_in_time->toDateString() . ' ' . $attendanceSettings->office_end_time;
            }

            $attendance->company_id = $attendanceUser->company_id;

            // condition from Link ATA
            // if outside is not allowed and shift is ended then avoid the attendence saving
            // Save attendance only if conditions allow (e.g., not past shift end if button not shown)
            if (
                (!$showClockIn || $showClockIn->show_clock_in_button === 'no') &&
                !now($globalSetting->timezone)->greaterThan($officeEndTime) ||
                ($showClockIn && $showClockIn->show_clock_in_button === 'yes')
            ) {
                $attendance->save();
            }

            return Reply::successWithData(__('messages.attendanceSaveSuccess'), ['time' => $now->format('h:i A'), 'ip' => $attendance->clock_in_ip, 'working_from' => $attendance->working_from]);
        }

        return Reply::error(__('messages.maxClockin'));
    }

    // Determines the applicable shift settings for the user based on schedule and time
    public function attendanceShift($defaultAttendanceSettings, $authUser, $company)
    {
        $globalSetting = GlobalSetting::first();

        // Check for previous day's shift
        $checkPreviousDayShift = EmployeeShiftSchedule::with('shift')->where('user_id', $authUser)
            ->where('date', now($company->timezone)->subDay()->toDateString())
            ->first();

        // Check for today's shift
        $checkTodayShift = EmployeeShiftSchedule::with('shift')->where('user_id', $authUser)
            ->where('date', now($company->timezone)->toDateString())
            ->first();

        // Calculate previous day's default shift times
        $backDayFromDefault = Carbon::parse(now($company->timezone)->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_start_time);

        $backDayToDefault = Carbon::parse(now($company->timezone)->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_end_time);

        if ($backDayFromDefault->gt($backDayToDefault)) {
            $backDayToDefault->addDay();
        }

        // Current time in UTC
        $nowTime = Carbon::createFromFormat('Y-m-d H:i:s', now($company->timezone)->toDateTimeString(), 'UTC');

        // Prioritize previous day's shift if current time is within it
        if ($checkPreviousDayShift && $nowTime->betweenIncluded($checkPreviousDayShift->shift_start_time, $checkPreviousDayShift->shift_end_time)) {
            $attendanceSettings = $checkPreviousDayShift;

        }
        // Fall back to previous day's default if within default window
        else if ($nowTime->betweenIncluded($backDayFromDefault, $backDayToDefault)) {
            $attendanceSettings = $defaultAttendanceSettings;

        }
        // Use today's shift if current time is within or past it
        else if ($checkTodayShift &&
            ($nowTime->betweenIncluded($checkTodayShift->shift_start_time, $checkTodayShift->shift_end_time) || $nowTime->gt($checkTodayShift->shift_end_time))
        ) {
            $attendanceSettings = $checkTodayShift;
        }
        // Use today's shift if early clock-in is allowed
        else if ($checkTodayShift && !is_null($checkTodayShift->shift->early_clock_in))
        {
            $attendanceSettings = $checkTodayShift;
        }
        // Use today's shift if flexible
        else if ($checkTodayShift && $checkTodayShift->shift->shift_type == 'flexible')
        {
            $attendanceSettings = $checkTodayShift;
        }
        // Default to default settings
        else {

            $attendanceSettings = $defaultAttendanceSettings;
        }

        // Return the shift if embedded, otherwise the settings
        if (isset($attendanceSettings->shift)) {
            return $attendanceSettings->shift;
        }

        return $attendanceSettings;

    }

    /**
     * Attempt to authenticate using a custom callback.
     *
     * @param \Illuminate\Http\Request $request
     * @param callable $next
     * @return mixed
     */
    protected function handleUsingCustomCallback($request, $next)
    {
        // Invoke the custom authentication callback
        $user = call_user_func(Fortify::$authenticateUsingCallback, $request);

        if (!$user) {
            $this->fireFailedEvent($request);
            /** @phpstan-ignore-next-line */
            return $this->throwFailedAuthenticationException($request);
        }

        // Log in the user and fire login event
        $this->guard->login($user, $request->filled('remember'));
        \session()->forget('isRtl');

        event(new \App\Events\UserLoginEvent($user, $request->ip()));
        return $next($request);
    }

    /**
     * Throw a failed authentication validation exception.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwFailedAuthenticationException($request)
    {
        // Increment rate limiter
        $this->limiter->increment($request);

        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Fire the failed authentication attempt event with the given arguments.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function fireFailedEvent($request)
    {
        event(new Failed(config('fortify.guard'), null, [
            Fortify::username() => $request->{Fortify::username()},
            'password' => $request->password,
        ]));
    }

    // Throws validation exception for reCAPTCHA failure
    public function googleRecaptchaMessage()
    {
        throw ValidationException::withMessages([
            'g-recaptcha-response' => [__('auth.recaptchaFailed')],
        ]);
    }

}