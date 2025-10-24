<?php

namespace App\Http\Controllers;

use App\DataTables\ShiftRotationDataTable;
use Carbon\Carbon;
use App\Models\Role;
use App\Helper\Reply;
use App\Models\Holiday;
use Illuminate\Http\Request;
use App\Models\EmployeeShift;
use App\Models\AttendanceSetting;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use App\Models\EmployeeShiftSchedule;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\ErrorCorrectionLevel;
use App\Http\Requests\AttendanceSetting\UpdateAttendanceSetting;

class AttendanceSettingController extends AccountBaseController
{
    /**
     * Constructor for the AttendanceSettingController.
     * Initializes the parent controller, sets the page title, and defines the active settings menu.
     * Applies middleware to restrict access to users with 'all' permission for managing attendance settings and ensures the attendance module is enabled.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.attendanceSettings';
        $this->activeSettingMenu = 'attendance_settings';
        $this->middleware(function ($request, $next) {
            // Restrict access if the user lacks 'all' permission for managing attendance settings or if the attendance module is not enabled
            abort_403(!(user()->permission('manage_attendance_setting') == 'all' && in_array('attendance', user_modules())));

            return $next($request);
        });
    }

    /**
     * Displays the attendance settings index page.
     * Loads attendance settings, roles, and IP addresses, and handles different tabs (attendance, shift, qrcode, shift-rotation).
     * Generates a QR code for the qrcode tab and renders the appropriate view based on the selected tab.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|array
     */
    public function index()
    {
        // Initialize IP addresses array and fetch attendance settings
        $this->ipAddresses = [];
        $this->attendanceSetting = AttendanceSetting::first();
        $this->monthlyReportRoles = json_decode($this->attendanceSetting->monthly_report_roles);
        $this->roles = Role::where('name', '<>', 'client')->get();

        // Parse IP addresses from settings if available
        if (json_decode($this->attendanceSetting->ip_address)) {
            $this->ipAddresses = json_decode($this->attendanceSetting->ip_address, true);
        }

        // Determine the active tab from the request
        $tab = request('tab');
        switch ($tab) {
            case 'shift':
                // Prepare data for the shift tab, including week map and employee shifts
                $this->weekMap = Holiday::weekMap();
                $this->employeeShifts = EmployeeShift::where('shift_name', '<>', 'Day Off')->get();
                $this->view = 'attendance-settings.ajax.shift';
                break;

            case 'qrcode':
                // Generate a QR code for login using the company hash
                $this->qr = Builder::create()
                    ->writer(new PngWriter())
                    ->encoding(new Encoding('UTF-8'))
                    ->data((route('settings.qr-login', ['hash' => company()->hash])))
                    ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                    ->size(300)
                    ->margin(10)
                    ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                    ->validateResult(false)
                    ->build();
                $this->view = 'attendance-settings.ajax.qrcode';
                break;

            case 'shift-rotation':
                // Redirect to the shift rotation method
                return $this->shiftRotation();
                break;

            default:
                // Default to the attendance settings tab
                $this->view = 'attendance-settings.ajax.attendance';
                break;
        }

        // Set the active tab
        $this->activeTab = $tab ?: 'attendance';

        // Handle AJAX requests by rendering the specific tab view
        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        // Render the main attendance settings view
        return view('attendance-settings.index', $this->data);
    }

    /**
     * Displays the shift rotation page.
     * Sets the page title and active tab, and renders the shift rotation DataTable.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function shiftRotation()
    {
        $this->pageTitle = 'app.menu.shiftRotation';
        $this->activeTab = request('tab') ?: 'overview';
        $this->view = 'attendance-settings.ajax.shift-rotation';
        $dataTable = new ShiftRotationDataTable(true);

        // Render the shift rotation view with the DataTable
        return $dataTable->render('attendance-settings.index', $this->data);
    }

    /**
     * Updates attendance settings based on the provided request.
     * Validates input, checks for conflicts between auto clock-in and QR code settings, and saves the updated settings.
     * Clears session data for attendance settings and company after saving.
     *
     * @param UpdateAttendanceSetting $request The validated request containing updated attendance settings.
     * @param int $id The ID of the attendance setting to update.
     * @return array JSON response indicating success or error.
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateAttendanceSetting $request, $id)
    {
        // Retrieve the company's attendance settings
        $setting = company()->attendanceSetting;
        $attendanceSetting = AttendanceSetting::find(company()->id);

        // Prevent enabling both auto clock-in and QR code simultaneously
        if (request()->auto_clock_in == 'yes' && $attendanceSetting->qr_enable == 1) {
            return Reply::error(__('messages.fristSignAndQrCodeError'));
        }

        // Update attendance settings with validated request data
        $setting->employee_clock_in_out = ($request->employee_clock_in_out == 'yes') ? 'yes' : 'no';
        $setting->radius_check = ($request->radius_check == 'yes') ? 'yes' : 'no';
        $setting->ip_check = ($request->ip_check == 'yes') ? 'yes' : 'no';
        $setting->radius = $request->radius;
        $setting->ip_address = json_encode($request->ip);
        $setting->alert_after = $request->alert_after;
        $setting->week_start_from = $request->week_start_from;
        $setting->alert_after_status = ($request->alert_after_status == 'on') ? 1 : 0;
        $setting->save_current_location = ($request->save_current_location) ? 1 : 0;
        $setting->allow_shift_change = ($request->allow_shift_change) ? 1 : 0;
        $setting->auto_clock_in = ($request->auto_clock_in) ? 'yes' : 'no';
        $setting->show_clock_in_button = ($request->show_clock_in_button == 'yes') ? 'yes' : 'no';
        $setting->auto_clock_in_location = $request->auto_clock_in_location;
        $setting->monthly_report = ($request->monthly_report) ? 1 : 0;
        $setting->monthly_report_roles = json_encode($request->monthly_report_roles);
        $setting->save();

        // Clear session data for attendance settings and company
        session()->forget(['attendance_setting', 'company']);

        // Return success response
        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Determines the appropriate employee shift for attendance based on the current time and user schedule.
     * Checks the previous day's shift, today's shift, and default settings to select the correct shift.
     *
     * @param mixed $defaultAttendanceSettings The default attendance settings to fall back on.
     * @return mixed The selected shift object.
     */
    public function attendanceShift($defaultAttendanceSettings)
    {
        // Check for the previous day's shift schedule
        $checkPreviousDayShift = EmployeeShiftSchedule::with('shift')
            ->where('user_id', user()->id)
            ->where('date', now(company()->timezone)->subDay()->toDateString())
            ->first();

        // Check for today's shift schedule
        $checkTodayShift = EmployeeShiftSchedule::with('shift')
            ->where('user_id', user()->id)
            ->where('date', now(company()->timezone)->toDateString())
            ->first();

        // Define default shift times for the previous day
        $backDayFromDefault = Carbon::parse(now(company()->timezone)->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_start_time);
        $backDayToDefault = Carbon::parse(now(company()->timezone)->subDay()->format('Y-m-d') . ' ' . $defaultAttendanceSettings->office_end_time);

        // Adjust end time if it crosses midnight
        if ($backDayFromDefault->gt($backDayToDefault)) {
            $backDayToDefault->addDay();
        }

        // Get current time in UTC
        $nowTime = Carbon::createFromFormat('Y-m-d H:i:s', now(company()->timezone)->toDateTimeString(), 'UTC');

        // Determine the appropriate shift based on current time and schedules
        if ($checkPreviousDayShift && $nowTime->betweenIncluded($checkPreviousDayShift->shift_start_time, $checkPreviousDayShift->shift_end_time)) {
            $attendanceSettings = $checkPreviousDayShift;
        } elseif ($nowTime->betweenIncluded($backDayFromDefault, $backDayToDefault)) {
            $attendanceSettings = $defaultAttendanceSettings;
        } elseif (
            $checkTodayShift &&
            (
                $nowTime->betweenIncluded($checkTodayShift->shift_start_time, $checkTodayShift->shift_end_time) ||
                $nowTime->gt($checkTodayShift->shift_end_time) ||
                (!$nowTime->betweenIncluded($checkTodayShift->shift_start_time, $checkTodayShift->shift_end_time) && $defaultAttendanceSettings->show_clock_in_button == 'no')
            )
        ) {
            $attendanceSettings = $checkTodayShift;
        } elseif ($checkTodayShift && !is_null($checkTodayShift->shift->early_clock_in)) {
            $attendanceSettings = $checkTodayShift;
        } else {
            $attendanceSettings = $defaultAttendanceSettings;
        }

        return $attendanceSettings->shift;
    }
}