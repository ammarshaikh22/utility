<?php

namespace App\Traits;

use App\Models\DashboardWidget;
use App\Models\Deal;
use App\Models\Leave;
use App\Models\Payment;
use App\Models\ProjectActivity;
use App\Models\ProjectTimeLog;
use App\Models\Task;
use App\Models\TaskboardColumn;
use App\Models\Ticket;
use App\Models\UserActivity;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Trait OverviewDashboard
 *
 * Provides functionality to display company-wide overview metrics
 * for the admin dashboard:
 * - Employee/Client/Project/Invoice counts
 * - Tasks, Tickets, Attendance
 * - Earnings and Timelog charts
 * - Leaves, Leads follow-up, Activities
 */
trait OverviewDashboard
{
    /**
     * Builds the data required for the **Overview Dashboard**.
     *
     * - Checks user permission
     * - Loads summary counts (clients, employees, invoices, etc.)
     * - Prepares widgets, earnings chart, timelog chart
     * - Loads pending leaves, tickets, tasks, lead follow-ups, activities
     *
     * @return void
     */
    public function overviewDashboard()
    {
        // Permission check
        $this->viewOverviewDashboard = user()->permission('view_overview_dashboard');
        abort_403($this->viewOverviewDashboard !== 'all');

        // Date range (from request or defaults)
        $this->startDate = request('startDate') != ''
            ? Carbon::createFromFormat($this->company->date_format, request('startDate'))
            : now($this->company->timezone)->startOfMonth();

        $this->endDate = request('endDate') != ''
            ? Carbon::createFromFormat($this->company->date_format, request('endDate'))
            : now($this->company->timezone);

        $startDate = $this->startDate->toDateString();
        $endDate   = $this->endDate->toDateString();

        $completedTaskColumn = TaskboardColumn::completeColumn();

        /**
         * =====================
         * DASHBOARD COUNTERS
         * =====================
         */
        $this->counts = DB::table('users')
            ->select(
                // Clients
                DB::raw('(select count(users.id) from `users`
                          inner join role_user on role_user.user_id=users.id
                          inner join roles on roles.id=role_user.role_id
                          WHERE roles.name = "client"
                          AND users.company_id = ' . company()->id . ') as totalClients'),

                // Active employees
                DB::raw('(select count(users.id) from `users`
                          inner join role_user on role_user.user_id=users.id
                          inner join roles on roles.id=role_user.role_id
                          WHERE roles.name = "employee"
                          and users.status = "active"
                          AND users.company_id = ' . company()->id . ') as totalEmployees'),

                // Projects
                DB::raw('(select count(projects.id) from `projects`
                          WHERE projects.company_id = ' . company()->id . ') as totalProjects'),

                // Unpaid invoices
                DB::raw('(select count(invoices.id) from `invoices`
                          where (status = "unpaid" or status = "partial")
                          AND invoices.company_id = ' . company()->id . ') as totalUnpaidInvoices'),

                // Hours logged & breaks
                DB::raw('(select sum(project_time_logs.total_minutes)
                          from `project_time_logs`
                          where approved = "1"
                          AND project_time_logs.company_id = ' . company()->id . ') as totalHoursLogged'),

                DB::raw('(select sum(project_time_log_breaks.total_minutes)
                          from `project_time_log_breaks`
                          WHERE project_time_log_breaks.company_id = ' . company()->id . ') as totalBreakMinutes'),

                // Completed & Pending tasks
                DB::raw('(select count(tasks.id) from `tasks`
                          where tasks.board_column_id=' . $completedTaskColumn->id . '
                          and is_private = "0"
                          AND tasks.company_id = ' . company()->id . ') as totalCompletedTasks'),

                DB::raw('(select count(tasks.id) from `tasks`
                          where tasks.board_column_id != ' . $completedTaskColumn->id . '
                          and is_private = "0"
                          and tasks.deleted_at IS NULL
                          AND tasks.company_id = ' . company()->id . ') as totalPendingTasks'),

                // Today’s attendance
                DB::raw('(select count(distinct(attendances.user_id)) from `attendances`
                          inner join users as atd_user on atd_user.id=attendances.user_id
                          inner join role_user on role_user.user_id=atd_user.id
                          inner join roles on roles.id=role_user.role_id
                          WHERE roles.name = "employee"
                          and attendances.clock_in_time >= "' . today(company()->timezone)->setTimezone('UTC')->toDateTimeString() . '"
                          and atd_user.status = "active"
                          AND attendances.company_id = ' . company()->id . ') as totalTodayAttendance'),

                // Tickets
                DB::raw('(select count(tickets.id) from `tickets`
                          where (status="open") and deleted_at IS NULL
                          AND tickets.company_id = ' . company()->id . ') as totalOpenTickets'),

                DB::raw('(select count(tickets.id) from `tickets`
                          where (status="resolved" or status="closed")
                          and deleted_at IS NULL
                          AND tickets.company_id = ' . company()->id . ') as totalResolvedTickets')
            )
            ->first();

        // Format hours logged
        $minutes = $this->counts->totalHoursLogged - $this->counts->totalBreakMinutes;
        $hours   = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        $timeLog = $hours . ' ' . __('app.hrs');
        if ($remainingMinutes > 0) {
            $timeLog .= ' ' . $remainingMinutes . ' ' . __('app.mins');
        }
        $this->counts->totalHoursLogged = $timeLog;

        // Active widgets
        $this->widgets = DashboardWidget::where('dashboard_type', 'admin-dashboard')->get();
        $this->activeWidgets = $this->widgets->filter(fn($value) => $value->status == '1')
                                             ->pluck('widget_name')
                                             ->toArray();

        /**
         * =====================
         * CHARTS
         * =====================
         */
        $this->earningChartData = $this->earningChart($startDate, $endDate);
        $this->timlogChartData  = $this->timelogChart($startDate, $endDate);

        /**
         * =====================
         * LIST DATA
         * =====================
         */
        $this->leaves = Leave::with('user', 'type')
            ->where('status', 'pending')
            ->whereBetween('leave_date', [$startDate, $endDate])
            ->get();

        $this->newTickets = Ticket::with('requester')
            ->where('status', 'open')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->orderByDesc('updated_at')
            ->get();

        $this->pendingTasks = Task::with('project', 'users', 'boardColumn')
            ->where('tasks.board_column_id', '<>', $completedTaskColumn->id)
            ->where('tasks.is_private', 0)
            ->orderByDesc('due_date')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->limit(15)
            ->get();

        // Leads follow-up
        $currentDate = now()->timezone($this->company->timezone)->toDateTimeString();
        $this->pendingLeadFollowUps = Deal::with('followup', 'leadAgent', 'leadAgent.user', 'leadAgent.user.employeeDetail', 'leadAgent.user.employeeDetail.designation')
            ->selectRaw('deals.id, leads.company_name, leads.client_name as client_name, deals.agent_id,
                (select lead_follow_up.next_follow_up_date
                 from lead_follow_up
                 where lead_follow_up.deal_id = deals.id
                 and DATE(lead_follow_up.next_follow_up_date) < "' . $currentDate . '"
                 ORDER BY lead_follow_up.created_at DESC
                 Limit 1) as follow_up_date_past,
                (select lead_follow.next_follow_up_date
                 from lead_follow_up as lead_follow
                 where lead_follow.deal_id = deals.id
                 and status = "incomplete"
                 ORDER BY lead_follow.created_at DESC
                 Limit 1) as follow_up_date_next')
            ->leftJoin('leads', 'leads.id', 'deals.lead_id')
            ->where('deals.next_follow_up', 'yes')
            ->groupBy('deals.id')
            ->get()
            ->filter(fn($value) =>
                $value->follow_up_date_past != null &&
                $value->follow_up_date_next == null &&
                $value->followup->status != 'completed'
            );

        // Activities
        $this->projectActivities = ProjectActivity::with('project')
            ->join('projects', 'projects.id', '=', 'project_activity.project_id')
            ->where('projects.company_id', company()->id)
            ->whereNull('projects.deleted_at')
            ->select('project_activity.*')
            ->limit(15)
            ->whereBetween('project_activity.created_at', [$startDate, $endDate])
            ->orderByDesc('project_activity.id')
            ->groupBy('project_activity.id')
            ->get();

        $this->userActivities = UserActivity::with('user')
            ->limit(15)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('id')
            ->get();

        // View path
        $this->view = 'dashboard.ajax.overview';
    }

    /**
     * Prepare Earnings Chart Data.
     *
     * Aggregates completed payments grouped by date.
     * Handles multi-currency by applying exchange rates.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function earningChart($startDate, $endDate)
    {
        $payments = Payment::join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->where('payments.status', 'complete')
            ->whereBetween('payments.paid_on', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->orderBy('paid_on', 'ASC')
            ->get([
                DB::raw('DATE_FORMAT(paid_on,"%d-%M-%y") as date'),
                DB::raw('YEAR(paid_on) year, MONTH(paid_on) month'),
                DB::raw('amount as total'),
                'currencies.id as currency_id',
                'currencies.exchange_rate',
                'payments.exchange_rate',
                'payments.default_currency_id'
            ]);

        $incomes = [];

        foreach ($payments as $invoice) {
            // Determine exchange rate
            if ((is_null($invoice->default_currency_id) && is_null($invoice->exchange_rate)) ||
                (!is_null($invoice->default_currency_id) && Company()->currency_id != $invoice->default_currency_id)) {
                $currency = Currency::findOrFail($invoice->currency_id);
                $exchangeRate = $currency->exchange_rate;
            } else {
                $exchangeRate = $invoice->exchange_rate;
            }

            // Initialize income bucket per date
            if (!isset($incomes[$invoice->date])) {
                $incomes[$invoice->date] = 0;
            }

            // Convert if currency differs
            if ($invoice->currency_id != $this->company->currency_id && $invoice->total > 0 && $exchangeRate > 0) {
                $incomes[$invoice->date] += floatval($invoice->total) * floatval($exchangeRate);
            } else {
                $incomes[$invoice->date] += round($invoice->total, 2);
            }
        }

        // Build chart dataset
        $graphData = collect(array_map(
            fn($date) => [
                'date' => $date,
                'total' => round($incomes[$date] ?? 0, 2),
            ],
            array_keys($incomes)
        ));

        // Sort by date
        $graphData = $graphData->sortBy(fn($item) => strtotime($item['date']));

        return [
            'labels' => $graphData->pluck('date'),
            'values' => $graphData->pluck('total')->toArray(),
            'colors' => [$this->appTheme->header_color],
            'name'   => __('app.earnings'),
        ];
    }

    /**
     * Prepare Timelog Chart Data.
     *
     * Aggregates total logged hours grouped by date.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function timelogChart($startDate, $endDate)
    {
        $timelogs = ProjectTimeLog::whereBetween('start_time', [$startDate, $endDate])
            ->where('project_time_logs.approved', 1)
            ->groupBy('date')
            ->orderBy('start_time', 'ASC')
            ->get([
                DB::raw('DATE_FORMAT(start_time,\'%d-%M-%y\') as date'),
                DB::raw('FLOOR(sum(total_minutes/60)) as total_hours')
            ]);

        return [
            'labels' => $timelogs->pluck('date'),
            'values' => $timelogs->pluck('total_hours')->toArray(),
            'colors' => [$this->appTheme->header_color],
            'name'   => __('modules.dashboard.totalHoursLogged'),
        ];
    }
}
