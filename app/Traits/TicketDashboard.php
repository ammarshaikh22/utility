<?php

namespace App\Traits;

use App\Models\DashboardWidget;
use App\Models\Ticket;
use App\Models\TicketChannel;
use App\Models\TicketType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Trait TicketDashboard
 *
 * Provides logic for generating ticket dashboard metrics, charts,
 * and recent activity data for the admin panel.
 */
trait TicketDashboard
{
    /**
     * Build the ticket dashboard view data.
     *
     * - Ensures the user has proper permissions.
     * - Loads widget configuration.
     * - Calculates ticket counts (resolved, unresolved, unassigned).
     * - Prepares charts for ticket type, status, and channel distribution.
     * - Loads recent open tickets.
     *
     * @return void
     */
    public function ticketDashboard(): void
    {
        // Check permission to view ticket dashboard
        $this->viewTicketDashboard = user()->permission('view_ticket_dashboard');
        abort_403($this->viewTicketDashboard !== 'all');

        $this->pageTitle = 'app.ticketDashboard';

        // Parse start and end dates, fallback to current month if not provided
        $this->startDate = (request('startDate') != '')
            ? Carbon::createFromFormat($this->company->date_format, request('startDate'))
            : now($this->company->timezone)->startOfMonth();

        $this->endDate = (request('endDate') != '')
            ? Carbon::createFromFormat($this->company->date_format, request('endDate'))
            : now($this->company->timezone);

        // Normalize to full-day timestamps
        $startDate = $this->startDate->startOfDay()->toDateTimeString();
        $endDate   = $this->endDate->endOfDay()->toDateTimeString();

        // Load available dashboard widgets for tickets
        $this->widgets = DashboardWidget::where('dashboard_type', 'admin-ticket-dashboard')->get();
        $this->activeWidgets = $this->widgets->filter(fn($value) => $value->status == '1')
            ->pluck('widget_name')
            ->toArray();

        // Calculate ticket counts in the given date range
        $ticketCounts = Ticket::select('id')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->selectRaw('
                SUM(CASE WHEN status IN ("open", "pending") THEN 1 ELSE 0 END) as totalUnresolvedTickets,
                SUM(CASE WHEN status IN ("resolved", "closed") THEN 1 ELSE 0 END) as totalResolvedTickets,
                SUM(CASE WHEN status IN ("open", "pending") AND agent_id IS NULL THEN 1 ELSE 0 END) as totalUnassignedTicket
            ')
            ->first();

        $this->totalUnresolvedTickets = $ticketCounts->totalUnresolvedTickets;
        $this->totalResolvedTickets   = $ticketCounts->totalResolvedTickets;
        $this->totalUnassignedTicket  = $ticketCounts->totalUnassignedTicket;

        // Prepare chart datasets
        $this->ticketTypeChart    = $this->ticketTypeChart($startDate, $endDate);
        $this->ticketStatusChart  = $this->ticketStatusChart($startDate, $endDate);
        $this->ticketChannelChart = $this->ticketChannelChart($startDate, $endDate);

        // Recent open tickets list
        $this->newTickets = Ticket::with('requester')
            ->where('status', 'open')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->orderByDesc('updated_at')
            ->get();

        // Define which Blade view to render
        $this->view = 'dashboard.ajax.ticket';
    }

    /**
     * Generate ticket type distribution chart.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function ticketTypeChart($startDate, $endDate): array
    {
        $tickets = TicketType::withCount([
            'tickets as tickets_within_date_range' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('updated_at', [$startDate, $endDate]);
            }
        ])->get();

        $data['labels'] = $tickets->pluck('type')->toArray();

        // Assign random but consistent colors based on MD5 hash of type name
        $data['colors'] = $data['labels']
            ? array_map(fn($value) => '#' . substr(md5($value), 0, 6), $data['labels'])
            : [];

        $data['values'] = $tickets->pluck('tickets_within_date_range')->toArray();

        return $data;
    }

    /**
     * Generate ticket status distribution chart.
     *
     * Predefined colors are mapped to known statuses.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function ticketStatusChart($startDate, $endDate): array
    {
        $statusCounts = Ticket::whereBetween('updated_at', [$startDate, $endDate])
            ->select(DB::raw('count(id) as totalTicket'), 'status')
            ->groupBy('status')
            ->pluck('totalTicket', 'status');

        $data['colors'] = [
            'closed'   => '#1d82f5',
            'pending'  => '#FCBD01',
            'resolved' => '#2CB100',
            'open'     => '#D30000',
        ];

        $data['labels'] = $statusCounts->keys()
            ->map(fn($status) => __('app.' . $status)) // Use translations for labels
            ->toArray();

        $data['values'] = $statusCounts->pluck('totalTicket')->toArray();

        return $data;
    }

    /**
     * Generate ticket channel distribution chart.
     *
     * Assigns a unique color to each channel name.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function ticketChannelChart($startDate, $endDate): array
    {
        $tickets = TicketChannel::withCount([
            'tickets' => function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('updated_at', [$startDate, $endDate]);
            }
        ])->get();

        $data['labels'] = $tickets->pluck('channel_name')->toArray();
        $data['colors'] = array_map(fn($value) => '#' . substr(md5($value), 0, 6), $data['labels']);
        $data['values'] = $tickets->pluck('tickets_count')->toArray();

        return $data;
    }
}
