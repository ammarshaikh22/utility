<?php

namespace App\Http\Controllers;

use App\DataTables\FinanceReportDataTable;
use App\Helper\Reply;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceReportController extends AccountBaseController
{

    /**
     * Constructor.
     * Initializes controller with base settings and assigns page title.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.financeReport';
    }

    /**
     * Display the main Finance Report page.
     * Loads currencies, projects, and client filters for report generation.
     *
     * @param FinanceReportDataTable $dataTable
     * @return mixed
     */
    public function index(FinanceReportDataTable $dataTable)
    {
        // Restrict access if user doesn’t have permission to view finance reports
        abort_403(user()->permission('view_finance_report') != 'all');

        // Initialize report filter data
        $this->fromDate = now($this->company->timezone)->startOfMonth();
        $this->toDate = now($this->company->timezone);
        $this->currencies = Currency::all();
        $this->currentCurrencyId = $this->company->currency_id;
        $this->projects = Project::allProjects();
        $this->clients = User::allClients();

        // Render the finance report index page
        return $dataTable->render('reports.finance.index', $this->data);
    }

    /**
     * Generate and return finance report chart data.
     * This includes income trend analysis (based on payments) with filters.
     *
     * Filters supported:
     * - Date range (start and end date)
     * - Project
     * - Client
     *
     * The data is converted to default company currency and formatted for chart visualization.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function financeChartData(Request $request)
    {
        // Set default start and end dates (current month)
        $startDate = now($this->company->timezone)->startOfMonth()->toDateString();
        $endDate = now($this->company->timezone)->toDateString();

        // Base query: completed payments joined with related tables
        $payments = Payment::join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->leftJoin('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->leftJoin('projects', 'projects.id', '=', 'payments.project_id')
            ->where('payments.status', 'complete');

        // Filter by start date if provided
        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
        }

        $payments = $payments->where(DB::raw('DATE(payments.`paid_on`)'), '>=', $startDate);

        // Filter by end date if provided
        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
        }

        $payments = $payments->where(DB::raw('DATE(payments.`paid_on`)'), '<=', $endDate);

        // Filter by specific project if selected
        if ($request->projectID != 'all' && !is_null($request->projectID)) {
            $payments = $payments->where('payments.project_id', '=', $request->projectID);
        }

        // Filter by client (either linked via project or invoice)
        if ($request->clientID != 'all' && !is_null($request->clientID)) {
            $clientId = $request->clientID;
            $payments = $payments->where(function ($query) use ($clientId) {
                $query->where('projects.client_id', $clientId)
                    ->orWhere('invoices.client_id', $clientId);
            });
        }

        // Retrieve filtered payment records with formatted dates
        $payments = $payments->orderBy('paid_on', 'ASC')
            ->get([
                DB::raw('DATE_FORMAT(paid_on,"%d-%M-%y") as date'),
                DB::raw('YEAR(paid_on) year, MONTH(paid_on) month'),
                DB::raw('amount as total'),
                'currencies.id as currency_id',
                'payments.exchange_rate',
                'payments.default_currency_id'
            ]);

        // Prepare an array to store income totals by date
        $incomes = array();

        foreach ($payments as $invoice) {

            // Determine correct exchange rate to use
            if (
                (is_null($invoice->default_currency_id) && is_null($invoice->exchange_rate)) ||
                (!is_null($invoice->default_currency_id) && Company()->currency_id != $invoice->default_currency_id)
            ) {
                $currency = Currency::findOrFail($invoice->currency_id);
                $exchangeRate = $currency->exchange_rate;
            } else {
                $exchangeRate = $invoice->exchange_rate;
            }

            // Initialize date key if not already set
            if (!isset($incomes[$invoice->date])) {
                $incomes[$invoice->date] = 0;
            }

            // Convert to default company currency if necessary
            if ($invoice->currency_id != $this->company->currency_id && $exchangeRate != 0) {
                $incomes[$invoice->date] += floatval($invoice->total) * floatval($exchangeRate);
            } else {
                $incomes[$invoice->date] += floatval($invoice->total);
            }
        }

        // Extract dates and format for chart plotting
        $dates = array_keys($incomes);
        $graphData = array();

        foreach ($dates as $date) {
            $graphData[] = [
                'date' => $date,
                'total' => isset($incomes[$date]) ? round($incomes[$date], 2) : 0,
            ];
        }

        // Sort income data chronologically by date
        usort($graphData, function ($a, $b) {
            $t1 = strtotime($a['date']);
            $t2 = strtotime($b['date']);
            return $t1 - $t2;
        });

        // Convert array to Laravel collection for convenience
        $graphData = collect($graphData);

        // Prepare chart data arrays for frontend visualization
        $data['labels'] = $graphData->pluck('date')->toArray();
        $data['values'] = $graphData->pluck('total')->toArray();
        $totalEarning = $graphData->sum('total');
        $data['colors'] = [$this->appTheme->header_color];
        $data['name'] = __('modules.dashboard.totalEarnings');

        // Store chart data for use in the view
        $this->chartData = $data;

        // Render the finance report chart view
        $html = view('reports.timelogs.chart', $this->data)->render();

        // Return data-only JSON response for AJAX calls
        return Reply::dataOnly([
            'status' => 'success',
            'html' => $html,
            'title' => $this->pageTitle,
            'totalEarnings' => currency_format($totalEarning, company()->currency_id)
        ]);
    }

}
