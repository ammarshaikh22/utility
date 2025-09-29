<?php

namespace App\Traits;

use App\Models\DashboardWidget;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectStatusSetting;
use App\Models\ProjectTimeLog;
use App\Models\ProjectTimeLogBreak;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Trait ProjectDashboard
 *
 * Provides helper methods for building the project dashboard view,
 * including statistics such as total projects, hours logged, overdue projects,
 * milestones, and project status charts.
 */
trait ProjectDashboard
{

    /**
     * Prepares all the data required for rendering the Project Dashboard.
     *
     * @return void
     */
    public function projectDashboard()
    {
        // Check if the logged-in user has permission to view project dashboard
        $this->viewProjectDashboard = user()->permission('view_project_dashboard');
        abort_403($this->viewProjectDashboard !== 'all'); // block if not allowed

        // Set the page title
        $this->pageTitle = 'app.projectDashboard';

        // Determine start & end dates based on request, else fallback to current month
        $this->startDate = (request('startDate') != '')
            ? Carbon::createFromFormat($this->company->date_format, request('startDate'))
            : now($this->company->timezone)->startOfMonth();

        $this->endDate = (request('endDate') != '')
            ? Carbon::createFromFormat($this->company->date_format, request('endDate'))
            : now($this->company->timezone);

        $todayDate = now(company()->timezone)->toDateString();
        $startDate = $this->startDate->toDateString();
        $endDate = $this->endDate->toDateString();

        // Count total projects started within the selected period
        $this->totalProject = Project::whereBetween(DB::raw('DATE(`start_date`)'), [$startDate, $endDate])
            ->count();

        /**
         * HOURS LOGGED CALCULATION
         */
        // Get total minutes logged (excluding breaks)
        $hoursLogged = ProjectTimeLog::whereDate('start_time', '>=', $startDate)
            ->whereDate('end_time', '<=', $endDate)
            ->whereNotNull('project_id')
            ->where('approved', 1)
            ->sum('total_minutes');

        // Subtract break minutes from total logged minutes
        $breakMinutes = ProjectTimeLogBreak::join('project_time_logs', 'project_time_log_breaks.project_time_log_id', '=', 'project_time_logs.id')
            ->whereDate('project_time_logs.start_time', '>=', $startDate)
            ->whereDate('project_time_logs.end_time', '<=', $endDate)
            ->whereNotNull('project_time_logs.project_id')
            ->sum('project_time_log_breaks.total_minutes');

        $hoursLogged = $hoursLogged - $breakMinutes;

        // Convert total minutes into "xh ym" format
        $hours = intdiv($hoursLogged, 60);
        $minutes = $hoursLogged % 60;
        $this->totalHoursLogged = $hours > 0
            ? $hours . 'h' . ($minutes > 0 ? ' ' . sprintf('%02dm', $minutes) : '')
            : ($minutes > 0 ? sprintf('%dm', $minutes) : '0s');

        /**
         * OVERDUE PROJECTS
         */
        if ($todayDate >= $startDate && $todayDate <= $endDate) {
            // Count projects with deadline between start date and today
            $this->totalOverdueProject = Project::whereNotNull('deadline')
                ->whereRaw('Date(projects.deadline) >= ?', [$startDate])
                ->whereRaw('Date(projects.deadline) < ?', [$todayDate])
                ->count();
        } else {
            // Count overdue projects within the date range
            $this->totalOverdueProject = Project::whereNotNull('deadline')
                ->whereBetween(DB::raw('DATE(`deadline`)'), [$startDate, $endDate])
                ->count();
        }

        /**
         * DASHBOARD WIDGETS
         */
        // Fetch admin dashboard widgets and only keep active ones
        $this->widgets = DashboardWidget::where('dashboard_type', 'admin-project-dashboard')->get();
        $this->activeWidgets = $this->widgets->filter(function ($value) {
            return $value->status == '1';
        })->pluck('widget_name')->toArray();

        /**
         * PENDING MILESTONES
         */
        $this->pendingMilestone = ProjectMilestone::whereBetween(DB::raw('DATE(project_milestones.`created_at`)'), [$startDate, $endDate])
            ->with('project', 'currency')
            ->whereHas('project')
            ->where('status', 'incomplete')
            ->get();

        /**
         * STATUS-WISE PROJECT DATA (for charts)
         */
        $this->statusWiseProject = $this->statusChartData($startDate, $endDate);

        // Set the blade view to render
        $this->view = 'dashboard.ajax.project';
    }

    /**
     * Prepares chart data for projects grouped by status within given date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function statusChartData($startDate, $endDate)
    {
        // Fetch active status names
        $labels = ProjectStatusSetting::where('status', 'active')->pluck('status_name');

        $data['labels'] = $labels;
        $data['colors'] = ProjectStatusSetting::where('status', 'active')->pluck('color');
        $data['values'] = [];

        // Count projects per status within the date range
        foreach ($labels as $label) {
            $data['values'][] = Project::whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate])
                ->where('status', $label)
                ->count();
        }

        return $data;
    }
}
