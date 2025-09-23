<?php

namespace App\Traits;

use App\Models\Contract;
use App\Models\ContractSign;
use App\Models\DashboardWidget;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadPipeline;
use App\Models\LeadSource;
use App\Models\PipelineStage;
use App\Models\Payment;
use App\Models\ProjectTimeLog;
use App\Models\Role;
use App\Models\User;
use App\Scopes\ActiveScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Trait ClientDashboard
 * Provides dashboard-related logic for client statistics, charts, and widgets.
 */
trait ClientDashboard
{
    /**
     * Prepares and sets all data needed for the client dashboard view.
     *
     * @return void
     */
    public function clientDashboard()
    {
        // Check if the logged-in user has permission to view client dashboard
        $this->viewClientDashboard = user()->permission('view_client_dashboard');
        abort_403($this->viewClientDashboard !== 'all');

        // Set page title
        $this->pageTitle = 'app.clientDashboard';

        // Handle date range (default: current month till today if not provided)
        $this->startDate = (request('startDate') != '') ? Carbon::createFromFormat($this->company->date_format, request('startDate')) : now($this->company->timezone)->startOfMonth();
        $this->endDate = (request('endDate') != '') ? Carbon::createFromFormat($this->company->date_format, request('endDate')) : now($this->company->timezone);
        $startDate = $this->startDate->toDateString();
        $endDate = $this->endDate->toDateString();

        // Pipeline filter (optional)
        $pipelineId = (request('pipelineId') != '') ? request('pipelineId') : null;

        // Count total new clients registered in the given date range
        $this->totalClient = User::withoutGlobalScope(ActiveScope::class)
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('client_details', 'users.id', '=', 'client_details.user_id')
            ->where('roles.name', 'client')
            ->whereBetween(DB::raw('DATE(client_details.`created_at`)'), [$startDate, $endDate])
            ->select('users.id')
            ->count();

        // Count leads created in the date range
        $this->totalLead = Lead::whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate])
            ->count();

        // Count deals created in the date range
        $this->totalDeals = Deal::whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate])
            ->count();

        // Get deals updated in the date range along with their pipeline stage
        $this->totalLeadConversions = Deal::select('deals.id', 'pipeline_stages.slug')
            ->whereBetween(DB::raw('DATE(deals.updated_at)'), [$startDate, $endDate])
            ->leftJoin('pipeline_stages', 'pipeline_stages.id', 'deals.pipeline_stage_id')
            ->get();

        // Count only "won" deals (successful conversions)
        $this->convertedDeals = $this->totalLeadConversions->filter(function ($value, $key) {
            return $value->slug == 'win';
        })->count();

        // Conversion percentage (won deals ÷ total conversions)
        $this->convertDealPercentage = ($this->totalLeadConversions->count() > 0)
            ? number_format(($this->convertedDeals / $this->totalLeadConversions->count() * 100), 2)
            : 0;

        // Count contracts generated in the date range (by start or end date)
        $this->totalContractsGenerated = Contract::whereBetween(DB::raw('DATE(contracts.`end_date`)'), [$startDate, $endDate])
            ->orWhereBetween(DB::raw('DATE(contracts.`start_date`)'), [$startDate, $endDate])
            ->count();

        // Count signed contracts
        $this->totalContractsSigned = ContractSign::whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate])
            ->count();

        // Recent login activities of clients
        $this->recentLoginActivities = Role::with(['users' => function ($query) use ($startDate, $endDate) {
            return $query->select('users.id', 'users.name', 'users.email', 'users.last_login', 'users.image')
                ->whereBetween(DB::raw('DATE(users.`last_login`)'), [$startDate, $endDate])
                ->whereNotNull('last_login')
                ->orderBy('users.last_login', 'desc')
                ->limit(10);
        }])->where('name', 'client')->first();

        // Latest registered clients
        $this->latestClient = Role::with([
            'users' => function ($query) use ($startDate, $endDate) {
                return $query->with('clientDetails:id,user_id,company_name')
                    ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'users.image', 'users.status')
                    ->whereBetween(DB::raw('DATE(users.`created_at`)'), [$startDate, $endDate])
                    ->orderBy('users.id', 'desc')
                    ->limit(10);
            }])
            ->where('name', 'client')
            ->first();

        // Earnings and timelog charts for clients
        $this->clientEarningChart = $this->clientEarningChart($startDate, $endDate);
        $this->clientTimelogChart = $this->clientTimelogChart($startDate, $endDate);

        // Lead pipeline setup
        $this->leadPipelines = LeadPipeline::all();
        $defaultPipeline = $this->leadPipelines->filter(function ($value, $key) {
            return $value->default == '1';
        })->first();
        $defaultPipelineId = ($pipelineId) ?: $defaultPipeline->id;

        // Lead status & source charts
        $this->leadStatusChart = $this->leadStatusChart($startDate, $endDate, $defaultPipelineId);
        $this->leadSourceChart = $this->leadSourceChart($startDate, $endDate);

        // Dashboard widgets setup
        $this->widgets = DashboardWidget::where('dashboard_type', 'admin-client-dashboard')->get();
        $this->activeWidgets = $this->widgets->filter(function ($value, $key) {
            return $value->status == '1';
        })->pluck('widget_name')->toArray();

        // View to render dashboard data
        $this->view = 'dashboard.ajax.client';
    }

    /**
     * Build earnings chart data for clients (payments grouped by client).
     */
    public function clientEarningChart($startDate, $endDate)
    {
        $payments = Payment::with('project', 'project.client', 'invoice', 'invoice.client')
            ->join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->leftJoin('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->leftJoin('projects', 'projects.id', '=', 'payments.project_id')
            ->select('payments.amount', 'currencies.id as currency_id', 'payments.exchange_rate', 'projects.client_id', 'invoices.client_id as invoice_client_id', 'payments.invoice_id', 'payments.project_id')
            ->where('payments.status', 'complete');

        // Only consider payments linked to a project or invoice
        $payments = $payments->where(function ($query) {
            $query->whereNotNull('projects.client_id')
                ->orWhereNotNull('invoices.client_id');
        });

        // Apply date range
        $payments = $payments->where(DB::raw('DATE(payments.`paid_on`)'), '>=', $startDate);
        $payments = $payments->where(DB::raw('DATE(payments.`paid_on`)'), '<=', $endDate);

        // Fetch payments
        $payments = $payments->orderBy('paid_on', 'ASC')->get();

        $chartDataClients = array();

        // Prepare client-wise earnings
        foreach ($payments as $chart) {
            // Determine client name (from project or invoice)
            if (is_null($chart->client_id)) {
                $chartName = $chart->invoice->client->name;
            } else {
                $chartName = $chart->project->client->name;
            }

            // Initialize if client not in array
            if (!array_key_exists($chartName, $chartDataClients)) {
                $chartDataClients[$chartName] = 0;
            }

            // Handle currency conversion (crypto & fiat separately)
            if ($chart->currency->currency_code != $this->company->currency->currency_code && $chart->currency->exchange_rate != 0) {
                if ($chart->currency->is_cryptocurrency == 'yes') {
                    $usdTotal = ($chart->amount * $chart->currency->usd_price);
                    $chartDataClients[$chartName] += round(floor(floatval($usdTotal) * floatval($chart->exchange_rate)), 2);
                } else {
                    $chartDataClients[$chartName] += round((floatval($chart->amount) * floatval($chart->exchange_rate)), 2);
                }
            } else {
                $chartDataClients[$chartName] += round($chart->amount, 2);
            }
        }

        // Prepare chart dataset
        $data['labels'] = array_keys($chartDataClients);
        $data['values'] = array_values($chartDataClients);
        $data['colors'] = [$this->appTheme->header_color];
        $data['name'] = __('app.earnings');

        return $data;
    }

    /**
     * Build time log chart data grouped by client.
     */
    public function clientTimelogChart($startDate, $endDate)
    {
        $allTimelogs = ProjectTimeLog::leftJoin('tasks', 'tasks.id', 'project_time_logs.task_id')
            ->leftJoin('projects as proj', 'proj.id', 'project_time_logs.project_id')
            ->leftJoin('projects', 'projects.id', 'tasks.project_id')
            ->leftJoin('users', 'users.id', 'projects.client_id')
            ->leftJoin('users as client', 'client.id', 'proj.client_id')
            ->where('project_time_logs.approved', 1)
            ->whereBetween(DB::raw('DATE(project_time_logs.`created_at`)'), [$startDate, $endDate])
            ->select('project_time_logs.*', 'client.name')
            ->get();

        $clientWiseTimelogs = array();

        // Sum hours per client
        foreach ($allTimelogs as $timelog) {
            if (!array_key_exists($timelog->name, $clientWiseTimelogs)) {
                $clientWiseTimelogs[$timelog->name] = 0;
            }

            $clientWiseTimelogs[$timelog->name] += $timelog->total_hours;
        }

        // Prepare chart dataset
        $data['labels'] = array_keys($clientWiseTimelogs);
        $data['values'] = array_values($clientWiseTimelogs);
        $data['colors'] = [$this->appTheme->header_color];
        $data['name'] = __('app.hour');

        return $data;
    }

    /**
     * Build chart data for lead statuses within a pipeline.
     */
    public function leadStatusChart($startDate, $endDate, $pipelineID = null)
    {
        $leadStatus = PipelineStage::withCount(['deals' => function ($query) use ($startDate, $endDate) {
            return $query->whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate]);
        }]);

        // Apply pipeline filter if provided
        if ($pipelineID) {
            $leadStatus->where('lead_pipeline_id', $pipelineID);
        }

        $leadStatus = $leadStatus->get();

        $stageData = [];

        // Build dataset: labels, colors, values
        foreach ($leadStatus as $key => $value) {
            $stageData['labels'][] = $value->name;
            $stageData['colors'][] = $value->label_color;
            $stageData['values'][] = $value->deals_count;
        }

        return $stageData;
    }

    /**
     * Build chart data for lead sources.
     */
    public function leadSourceChart($startDate, $endDate)
    {
        $leadStatus = LeadSource::withCount(['leads' => function ($query) use ($startDate, $endDate) {
            return $query->whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate]);
        }])->get();

        $data['labels'] = [];

        // Generate translated labels
        foreach ($leadStatus->pluck('type') as $key => $value) {
            $labelName = current(explode(' ', $value));
            $data['labels'][] = __('app.' . strtolower($labelName)) . '' . strstr($value, ' ');
        }

        // Generate dynamic colors
        foreach ($data['labels'] as $key => $value) {
            $data['colors'][] = '#' . substr(md5($value), 0, 6);
        }

        // Count leads per source
        $data['values'] = $leadStatus->pluck('leads_count')->toArray();

        return $data;
    }
}
