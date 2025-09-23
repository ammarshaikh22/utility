<?php

namespace App\Traits;

use Exception;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\Leave;
use App\Models\Designation;
use App\Models\DashboardWidget;
use App\Models\EmployeeDetails;
use Illuminate\Support\Facades\DB;

/**
 * Trait HRDashboard
 *
 * Provides methods for building the **HR Dashboard** with widgets, charts, 
 * and statistics about employees, attendance, leaves, and attrition.
 *
 * Features:
 * - Validates user permission to view the HR dashboard
 * - Calculates employee statistics (total employees, new hires, exits, leaves, attendance, etc.)
 * - Builds chart data for department, designation, gender, role, headcount, and joining vs attrition
 * - Retrieves birthdays and late attendance
 */
trait HRDashboard
{
    use CurrencyExchange;

    /**
     * Main entry point for preparing HR dashboard data.
     *
     * Sets up:
     * - Page title, date filters (startDate & endDate)
     * - Active dashboard widgets
     * - Various HR statistics (leaves, employees, attendance, new hires, exits, etc.)
     * - Chart data (department-wise, designation-wise, gender-wise, role-wise, headcount, attrition)
     * - Extra lists (birthdays, top leave takers, late attendance)
     *
     * @return void
     */
    public function hrDashboard()
    {
        // Check user permission
        $this->viewHRDashboard = user()->permission('view_hr_dashboard');
        abort_403($this->viewHRDashboard !== 'all');

        // Setup page and date filters
        $this->pageTitle = 'app.hrDashboard';
        $this->startDate = request('startDate') 
            ? Carbon::createFromFormat($this->company->date_format, request('startDate')) 
            : now($this->company->timezone)->startOfMonth();

        $this->endDate = request('endDate') 
            ? Carbon::createFromFormat($this->company->date_format, request('endDate')) 
            : now($this->company->timezone);

        $startDate = $this->startDate->toDateString();
        $endDate = $this->endDate->toDateString();

        // Get HR dashboard widgets
        $this->widgets = DashboardWidget::where('dashboard_type', 'admin-hr-dashboard')->get();
        $this->activeWidgets = $this->widgets
            ->filter(fn($value) => $value->status == '1')
            ->pluck('widget_name')
            ->toArray();

        // Core HR statistics
        $this->totalLeavesApproved = Leave::whereBetween(DB::raw('DATE(`leave_date`)'), [$startDate, $endDate])
            ->where('status', 'approved')
            ->count();

        $this->totalEmployee = User::allEmployees(null, true)->count();
        $this->totalNewEmployee = EmployeeDetails::whereBetween(DB::raw('DATE(`joining_date`)'), [$startDate, $endDate])->count();
        $this->totalEmployeeExits = EmployeeDetails::whereBetween(DB::raw('DATE(`last_date`)'), [$startDate, $endDate])->count();

        // Attendance analysis
        $attandance = EmployeeDetails::join('users', 'users.id', 'employee_details.user_id')
            ->join('attendances', 'attendances.user_id', 'users.id')
            ->whereBetween(DB::raw('DATE(attendances.`clock_in_time`)'), [$startDate, $endDate])
            ->select(
                DB::raw('count(users.id) as employeeCount'), 
                DB::raw('DATE(attendances.clock_in_time) as date')
            )
            ->groupBy('date')
            ->get();

        // Calculate average attendance percentage
        if ($attandance->count() > 0) {
            try {
                $this->averageAttendance = number_format(
                    ((array_sum(array_column($attandance->toArray(), 'employeeCount')) / $attandance->count()) * 100) / $this->totalEmployee,
                    2
                ) . '%';
            } catch (Exception $e) {
                $this->averageAttendance = '0%';
            }
        } else {
            $this->averageAttendance = '0%';
        }

        // Charts
        $this->departmentWiseChart = $this->departmentWiseChart();
        $this->designationWiseChart = $this->designationWiseChart();
        $this->genderWiseChart = $this->genderWiseChart();
        $this->roleWiseChart = $this->roleWiseChart();
        $this->headCountChart = $this->headcountChart();
        $this->joiningVsAttritionChart = $this->joiningVsAttritionChart();

        // Leaves taken by employees
        $this->leavesTaken = User::with('employeeDetail', 'employeeDetail.designation')
            ->join('leaves', 'leaves.user_id', 'users.id')
            ->whereBetween(DB::raw('DATE(leaves.`leave_date`)'), [$startDate, $endDate])
            ->where('leaves.status', 'approved')
            ->select(DB::raw('count(leaves.id) as employeeLeaveCount'), 'users.*')
            ->groupBy('users.id')
            ->orderByDesc('employeeLeaveCount')
            ->get();

        // Birthdays within selected date range
        $fromMonthDay = Carbon::parse($startDate)->format('m-d');
        $tillMonthDay = Carbon::parse($endDate)->format('m-d');

        $this->birthdays = EmployeeDetails::with('user')
            ->whereNotNull('date_of_birth')
            ->where(function ($query) use ($fromMonthDay, $tillMonthDay) {
                $query->whereRaw('DATE_FORMAT(`date_of_birth`, "%m-%d") BETWEEN "' . $fromMonthDay . '" AND "' . $tillMonthDay . '"');
            })
            ->orderBy(DB::raw('MONTH(date_of_birth)'))
            ->get();

        // Late attendance marks
        $this->lateAttendanceMarks = User::with('employeeDetail', 'employeeDetail.designation')
            ->without(['role', 'clientDetails'])
            ->join('attendances', 'attendances.user_id', 'users.id')
            ->whereBetween(DB::raw('DATE(attendances.`clock_in_time`)'), [$startDate, $endDate])
            ->where('late', 'yes')
            ->select(DB::raw('count(DISTINCT DATE(attendances.clock_in_time)) as employeeLateCount'), 'users.*')
            ->groupBy('users.id')
            ->orderByDesc('employeeLateCount')
            ->get();

        // Current day employee vs attendance count
        $this->counts = User::select(
            DB::raw('(select count(distinct(attendances.user_id)) from attendances inner join users as atd_user on atd_user.id=attendances.user_id inner join role_user on role_user.user_id=atd_user.id inner join roles on roles.id=role_user.role_id WHERE roles.name = "employee" and attendances.clock_in_time >= "' . today(company()->timezone)->setTimezone('UTC')->toDateTimeString() . '" and atd_user.status = "active" AND attendances.company_id = ' . company()->id . ') as totalTodayAttendance'),
            DB::raw('(select count(users.id) from users inner join role_user on role_user.user_id=users.id inner join roles on roles.id=role_user.role_id WHERE roles.name = "employee" and users.status = "active" AND users.company_id = ' . company()->id . ') as totalEmployees')
        )->first();

        // Set dashboard view template
        $this->view = 'dashboard.ajax.hr';
    }

    /**
     * Build department-wise employee distribution chart.
     *
     * @return array
     */
    public function departmentWiseChart()
    {
        $departments = Team::withCount(['teamMembers' => function ($query) {
            $query->join('users', 'users.id', '=', 'employee_details.user_id')
                  ->where('users.status', '=', 'active');
        }])->get();

        $data['labels'] = $departments->pluck('team_name')->toArray();
        $data['colors'] = array_map(fn($value) => '#' . substr(md5($value), 0, 6), $data['labels']);
        $data['values'] = $departments->pluck('team_members_count')->toArray();

        return $data;
    }

    /**
     * Build designation-wise employee distribution chart.
     *
     * @return array
     */
    public function designationWiseChart()
    {
        $designations = Designation::withCount(['members' => function ($query) {
            $query->join('users', 'users.id', '=', 'employee_details.user_id')
                  ->where('users.status', '=', 'active');
        }])->get();

        $data['labels'] = $designations->pluck('name')->toArray();
        $data['colors'] = array_map(fn($value) => '#' . substr(md5($value), 0, 6), $data['labels']);
        $data['values'] = $designations->pluck('members_count')->toArray();

        return $data;
    }

    /**
     * Build gender-wise employee distribution chart.
     *
     * @return array
     */
    public function genderWiseChart()
    {
        $genderWiseEmployee = EmployeeDetails::join('users', 'users.id', 'employee_details.user_id')
            ->select(DB::raw('count(employee_details.id) as totalEmployee'), 'users.gender')
            ->whereNotNull('users.gender')
            ->where('users.status', '=', 'active')
            ->groupBy('users.gender')
            ->orderBy('users.gender', 'ASC')
            ->get();

        $labels = $genderWiseEmployee->pluck('gender')->toArray();

        $data['labels'] = array_map(fn($value) => __('app.' . $value), $labels);
        $data['values'] = $genderWiseEmployee->pluck('totalEmployee')->toArray();
        $data['colors'] = ['#1d82f5', '#FCBD01', '#D30000'];

        return $data;
    }

    /**
     * Build role-wise employee distribution chart.
     *
     * @return array
     */
    public function roleWiseChart()
    {
        $roleWiseChart = Role::withCount(['users' => fn($query) => $query->where('users.status', '=', 'active')])
            ->where('name', '<>', 'client')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($roleWiseChart as $value) {
            if (in_array($value->name, ['admin', 'employee'])) {
                $data['labels'][] = __('app.' . $value->name);
            } else {
                $data['labels'][] = $value->display_name;
            }
            $data['colors'][] = '#' . substr(md5($value->name ?? $value->display_name), 0, 6);
        }

        $data['values'] = $roleWiseChart->pluck('users_count')->toArray();

        return $data;
    }

    /**
     * Build headcount trend chart (employee growth over time).
     *
     * @return array
     */
    public function headCountChart()
    {
        $period = now(global_setting()->timezone)->subMonths(11)->monthsUntil(now(global_setting()->timezone));
        $startDate = $period->startDate->startOfMonth();
        $endDate = $period->endDate->endOfMonth();

        // Initialize months
        $months = [];
        foreach ($period as $periodData) {
            $months[$periodData->format('m-Y')] = [
                'y' => $periodData->translatedFormat('F'),
                'a' => 0
            ];
        }

        // Count employees before period start
        $oldEmployee = EmployeeDetails::whereDate('joining_date', '<', $startDate)->count();
        $inActiveOldEmployee = EmployeeDetails::whereHas('user', fn($q) => $q->where('status', '=', 'deactive')->where('last_date', '<', $startDate))->count();
        $oldEmployee -= $inActiveOldEmployee;

        // Employees joined during period
        $joiningDates = EmployeeDetails::whereBetween('joining_date', [$startDate, $endDate])
            ->select(DB::raw('count(*) as data'), DB::raw("DATE_FORMAT(joining_date, '%m-%Y') date"))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Employees exited during period
        $lastDates = EmployeeDetails::whereBetween('last_date', [$startDate, $endDate])
            ->select(DB::raw('count(*) as data'), DB::raw("DATE_FORMAT(last_date, '%m-%Y') date"))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Employees deactivated during period
        $inActiveEmployee = EmployeeDetails::join('users', 'employee_details.user_id', '=', 'users.id')
            ->whereBetween('last_date', [$startDate, $endDate])
            ->select(DB::raw('count(*) as data'), DB::raw("DATE_FORMAT(last_date, '%m-%Y') date"))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Build graph data
        $graphData = [];
        foreach ($months as $key => $month) {
            $inActiveCount = $inActiveEmployee[$key]->data ?? 0;
            $oldEmployee = $oldEmployee + ($joiningDates[$key]->data ?? 0) - $inActiveCount - ($lastDates[$key]->data ?? 0);

            $graphData[] = ['y' => $month['y'], 'a' => $oldEmployee];
        }

        $graphData = collect($graphData);

        $data['labels'] = $graphData->pluck('y');
        $data['values'] = $graphData->pluck('a')->toArray();
        $data['colors'] = [$this->appTheme->header_color];
        $data['name'] = __('modules.dashboard.headcount');

        return $data;
    }

    /**
     * Build joining vs attrition comparison chart.
     *
     * @return array
     */
    public function joiningVsAttritionChart()
    {
        $period = now()->subMonths(11)->monthsUntil(now());
        $startDate = $period->startDate->startOfMonth();
        $endDate = $period->endDate->endOfMonth();

        // Initialize months
        $months = [];
        foreach ($period as $periodData) {
            $months[$periodData->format('m-Y')] = [
                'y' => $periodData->translatedFormat('F'),
                'a' => 0,
                'b' => 0
            ];
        }

        // Joining counts
        $joiningDates = EmployeeDetails::whereBetween('joining_date', [$startDate, $endDate])
            ->select(DB::raw('count(joining_date) as data'), DB::raw("DATE_FORMAT(joining_date, '%m-%Y') date"))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Attrition counts
        $attritionDates = EmployeeDetails::join('users', 'employee_details.user_id', '=', 'users.id')
            ->where('users.status', '=', 'deactive')
            ->whereBetween('last_date', [$startDate, $endDate])
            ->select(DB::raw('count(last_date) as data'), DB::raw("DATE_FORMAT(last_date, '%m-%Y') date"))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Build graph data
        $graphData = [];
        foreach ($months as $key => $month) {
            $joinings = $joiningDates[$key]->data ?? 0;
            $exit = $attritionDates[$key]->data ?? 0;

            $graphData[] = ['y' => $month['y'], 'a' => $joinings, 'b' => $exit];
        }

        $graphData = collect($graphData);

        $data['labels'] = $graphData->pluck('y');
        $data['values'][] = $graphData->pluck('a'); // joinings
        $data['values'][] = $graphData->pluck('b'); // attrition
        $data['colors'] = ['#1D82F5', '#d30000'];
        $data['name'] = [__('app.joining'), __('app.attrition')];

        return $data;
    }
}
