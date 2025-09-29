<?php

namespace App\Traits;

use App\Models\ContractSign;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectStatusSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Helper\UserService;

/**
 * Trait ClientPanelDashboard
 * Handles data preparation for the client’s personal dashboard panel.
 */
trait ClientPanelDashboard
{
    /**
     * Prepare dashboard data for a client (projects, invoices, contracts, milestones, etc.)
     *
     * @return \Illuminate\Http\Response
     */
    public function clientPanelDashboard()
    {
        // Get permission for viewing invoices
        $viewInvoicePermission = user()->permission('view_invoices');

        // Get the currently logged-in client ID
        $id = UserService::getUserId();

        // Load available modules for the user
        $this->modules = user_modules();

        // Count total projects and unresolved tickets for the client
        $this->counts = User::select(
            DB::raw('(select count(projects.id) from `projects` where client_id = ' . $id . ' and deleted_at IS NULL and projects.company_id = ' . company()->id . ') as totalProjects'),
            DB::raw('(select count(tickets.id) from `tickets` where (status="open" or status="pending") and user_id = ' . $id . '  and tickets.company_id = ' . company()->id . ' and deleted_at IS NULL) as totalUnResolvedTickets')
        )->first();

        // ----------------- Invoices Section -----------------

        // Total paid invoices
        $this->totalPaidInvoice = Invoice::where(function ($query) {
                $query->where('invoices.status', 'paid');
            })
            ->where('invoices.client_id', $id)
            ->where('invoices.send_status', 1) // must be sent
            ->where('invoices.credit_note', 0) // exclude credit notes
            ->select('invoices.id');

        // If permission is limited to 'added', filter by invoices created by this client
        if ($viewInvoicePermission == 'added') {
            $this->totalPaidInvoice = $this->totalPaidInvoice->where('invoices.added_by', $id);
        }

        // Count total paid invoices
        $this->totalPaidInvoice = $this->totalPaidInvoice->count();

        // Total unpaid/partially paid invoices
        $this->totalUnPaidInvoice = Invoice::where(function ($query) {
                $query->where('invoices.status', 'unpaid')
                    ->orWhere('invoices.status', 'partial');
            })
            ->where('invoices.client_id', $id)
            ->where('invoices.send_status', 1)
            ->where('invoices.credit_note', 0)
            ->select('invoices.id');

        // If permission is limited, apply filter
        if ($viewInvoicePermission == 'added') {
            $this->totalUnPaidInvoice = $this->totalUnPaidInvoice->where('invoices.added_by', $id);
        }

        // Count total unpaid invoices
        $this->totalUnPaidInvoice = $this->totalUnPaidInvoice->count();

        // ----------------- Contracts Section -----------------

        // Total signed contracts for this client
        $this->totalContractsSigned = ContractSign::whereHas('contract', function ($query) use ($id) {
            $query->where('client_id', $id);
        })->count();

        // ----------------- Milestones Section -----------------

        // Get permission for viewing project milestones
        $this->viewMilestonePermission = user()->permission('view_project_milestones');

        $this->pendingMilestone = ProjectMilestone::query();

        // If user has permission, fetch incomplete milestones linked to the client
        if ($this->viewMilestonePermission != 'none') {
            $this->pendingMilestone = ProjectMilestone::with('project', 'currency')
                ->whereHas('project', function ($query) use ($id) {
                    $query->where('client_id', $id);
                })
                ->where('status', 'incomplete')
                ->get();
        }

        // ----------------- Project Status Section -----------------

        // Prepare chart data for client’s projects grouped by status
        $this->statusWiseProject = $this->projectStatusChartData();

        // Return dashboard view with all prepared data
        return view('dashboard.client.index', $this->data);
    }

    /**
     * Generate chart data for projects grouped by their status (active statuses only).
     *
     * @return array
     */
    public function projectStatusChartData()
    {
        // Fetch active project status labels
        $labels = ProjectStatusSetting::where('status', 'active')->pluck('status_name');

        // Build chart dataset
        $data['labels'] = ProjectStatusSetting::where('status', 'active')->pluck('status_name');
        $data['colors'] = ProjectStatusSetting::where('status', 'active')->pluck('color');
        $data['values'] = [];

        $id = UserService::getUserId();

        // Count projects for each status belonging to this client
        foreach ($labels as $label) {
            $data['values'][] = Project::where('client_id', $id)
                ->where('status', $label)
                ->count();
        }

        return $data;
    }
}
