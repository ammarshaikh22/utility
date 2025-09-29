<?php

namespace App\Traits;

use App\Models\DashboardWidget;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Proposal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Trait FinanceDashboard
 *
 * Provides reusable functionality to build and manage
 * the Admin Finance Dashboard. Includes logic for:
 * - Permissions
 * - Invoice/Expense/Payment aggregations
 * - Earnings & pending amounts
 * - Chart data preparation (invoices, estimates, proposals, projects).
 */
trait FinanceDashboard
{
    use CurrencyExchange, ClientDashboard;

    /**
     * Prepare data for the Finance Dashboard view.
     *
     * Includes:
     * - Permission checks
     * - Widget loading
     * - Aggregated financial metrics (expenses, earnings, pending invoices)
     * - Chart data for invoices, estimates, proposals, clients, and projects
     *
     * @return void
     */
    public function financeDashboard()
    {
        // Permission check: user must have "all" level finance dashboard access
        $this->viewFinanceDashboard = user()->permission('view_finance_dashboard');
        abort_403($this->viewFinanceDashboard !== 'all');

        // Determine reporting date range
        $this->startDate = request('startDate') != ''
            ? Carbon::createFromFormat($this->company->date_format, request('startDate'))
            : now($this->company->timezone)->startOfMonth();

        $this->endDate = request('endDate') != ''
            ? Carbon::createFromFormat($this->company->date_format, request('endDate'))
            : now($this->company->timezone);

        $startDate = $this->startDate->toDateString();
        $endDate = $this->endDate->toDateString();

        // Load enabled dashboard widgets
        $this->widgets = DashboardWidget::where('dashboard_type', 'admin-finance-dashboard')->get();
        $this->activeWidgets = $this->widgets
            ->filter(fn($value) => $value->status == '1')
            ->pluck('widget_name')
            ->toArray();

        /**
         * ==============================
         * INVOICES
         * ==============================
         */
        // Count fully paid invoices
        $this->totalPaidInvoice = Invoice::where('status', 'paid')
            ->whereBetween(DB::raw('DATE(`issue_date`)'), [$startDate, $endDate])
            ->count();

        // Count unpaid and partially paid invoices
        $this->totalUnPaidInvoice = Invoice::where(function ($query) {
                return $query->where('status', 'unpaid')->orWhere('status', 'partial');
            })
            ->whereBetween(DB::raw('DATE(`issue_date`)'), [$startDate, $endDate])
            ->count();

        /**
         * ==============================
         * EXPENSES
         * ==============================
         */
        $expenses = Expense::whereBetween(DB::raw('DATE(expenses.`purchase_date`)'), [$startDate, $endDate])
            ->join('currencies', 'currencies.id', '=', 'expenses.currency_id')
            ->select(
                'expenses.id',
                'expenses.price',
                'expenses.exchange_rate as expenseExchangeRate',
                'currencies.currency_code',
                'currencies.is_cryptocurrency',
                'currencies.usd_price',
                'currencies.exchange_rate'
            )
            ->where('expenses.status', 'approved')
            ->get();

        $totalExpenses = 0;

        foreach ($expenses as $expense) {
            // Convert expense into base currency using exchange rate
            $defaultPrice = floatval($expense->price) * floatval($expense->expenseExchangeRate);
            $totalExpenses += $defaultPrice;
        }

        $this->totalExpenses = round($totalExpenses, 2);

        /**
         * ==============================
         * EARNINGS
         * ==============================
         */
        $paymentsModal = Payment::whereBetween(DB::raw('DATE(payments.`paid_on`)'), [$startDate, $endDate]);

        $payments = (clone $paymentsModal)
            ->join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->where('payments.status', 'complete')
            ->select(
                DB::raw('(payments.amount) as total'),
                'currencies.currency_code',
                'currencies.is_cryptocurrency',
                'currencies.usd_price',
                'currencies.exchange_rate',
                'currencies.id as currency_id',
                'payments.exchange_rate',
            )
            ->get();

        $totalEarnings = 0;

        foreach ($payments as $payment) {
            // Convert to company currency if needed
            if (isset($payment->currency)
                && $payment->currency->currency_code != $this->company->currency->currency_code
                && $payment->exchange_rate != 0
            ) {
                if ($payment->currency->is_cryptocurrency == 'yes') {
                    // Crypto → USD → Company currency
                    $usdTotal = (floatval($payment->total) * floatval($payment->currency->usd_price));
                    $totalEarnings += floor(floatval($usdTotal) * floatval($payment->currency->exchange_rate));
                } else {
                    $totalEarnings += floatval($payment->total) * floatval($payment->exchange_rate);
                }
            } else {
                $totalEarnings += $payment->total;
            }
        }

        $this->totalEarnings = round($totalEarnings, 2);

        /**
         * ==============================
         * PENDING INVOICES
         * ==============================
         */
        $invoices = Invoice::whereBetween(DB::raw('DATE(invoices.`issue_date`)'), [$startDate, $endDate])
            ->join('currencies', 'currencies.id', '=', 'invoices.currency_id')
            ->where(fn($q) => $q->where('invoices.status', 'unpaid')->orWhere('invoices.status', 'partial'))
            ->get();

        $totalPendingAmount = 0;

        foreach ($invoices as $invoice) {
            // Convert pending amount into company currency
            if ($invoice->currency->currency_code != $this->company->currency->currency_code
                && $invoice->currency->exchange_rate != 0
            ) {
                if ($invoice->currency->is_cryptocurrency == 'yes') {
                    $usdTotal = ($invoice->due_amount * $invoice->currency->usd_price);
                    $totalPendingAmount += floor(floatval($usdTotal) * floatval($invoice->currency->exchange_rate));
                } else {
                    $totalPendingAmount += floatval($invoice->due_amount) * floatval($invoice->currency->exchange_rate);
                }
            } else {
                $totalPendingAmount += $invoice->due_amount;
            }
        }

        $this->totalPendingAmount = round($totalPendingAmount, 2);

        /**
         * ==============================
         * CHART DATA
         * ==============================
         */
        $this->invoiceOverviewChartData = $this->invoiceOverviewChartData($startDate, $endDate);
        $this->estimateOverviewChartData = $this->estimateOverviewChartData($startDate, $endDate);
        $this->proposalOverviewChartData = $this->proposalOverviewChartData($startDate, $endDate);
        $this->clientEarningChart = $this->clientEarningChart($startDate, $endDate);
        $this->projectEarningChartData = $this->projectEarningChartData($startDate, $endDate);

        // Set view for rendering
        $this->view = 'dashboard.ajax.finance';
    }

    /**
     * Prepare chart data for invoices (status distribution).
     */
    public function invoiceOverviewChartData($startDate, $endDate)
    {
        $data['values'] = [];
        $data['colors'] = [];

        $allInvoice = Invoice::whereBetween(DB::raw('DATE(`issue_date`)'), [$startDate, $endDate])->get();

        // Each status count → add value and matching color
        $data['values'][] = $allInvoice->where('status', 'draft')->count();
        $data['colors'][] = '#1d82f5';

        $data['values'][] = $allInvoice->where('send_status', 0)->count();
        $data['colors'][] = '#4d4f5c';

        $data['values'][] = $allInvoice->where('status', 'unpaid')->count();
        $data['colors'][] = '#D30000';

        $data['values'][] = $allInvoice->filter(fn($value) =>
            ($value->status == 'unpaid' || $value->status == 'partial') && $value->due_date->lessThan(now())
        )->count();
        $data['colors'][] = '#99A5B5';

        $data['values'][] = $allInvoice->where('status', 'partial')->count();
        $data['colors'][] = '#FCBD01';

        $data['values'][] = $allInvoice->where('status', 'paid')->count();
        $data['colors'][] = '#2CB100';

        $data['labels'] = [
            __('modules.dashboard.invoiceDraft'),
            __('modules.dashboard.invoiceNotSent'),
            __('modules.dashboard.invoiceUnpaid'),
            __('modules.dashboard.invoiceOverdue'),
            __('modules.dashboard.invoicePartiallyPaid'),
            __('modules.dashboard.invoicePaid')
        ];

        return $data;
    }

    /**
     * Prepare chart data for estimates (status distribution).
     */
    public function estimateOverviewChartData($startDate, $endDate)
    {
        $data['values'] = [];
        $data['colors'] = [];

        $allEstimate = Estimate::whereBetween(DB::raw('DATE(`valid_till`)'), [$startDate, $endDate])->get();

        $data['values'][] = $allEstimate->where('status', 'draft')->count();
        $data['colors'][] = '#1d82f5';

        $data['values'][] = $allEstimate->where('send_status', 0)->count();
        $data['colors'][] = '#4d4f5c';

        $data['values'][] = $allEstimate->where('send_status', 1)->count();
        $data['colors'][] = '#FCBD01';

        $data['values'][] = $allEstimate->where('status', 'declined')->count();
        $data['colors'][] = '#99A5B5';

        $data['values'][] = $allEstimate->filter(fn($v) => $v->status == 'waiting' && $v->valid_till->lessThan(now()))->count();
        $data['colors'][] = '#D30000';

        $data['values'][] = $allEstimate->where('status', 'accepted')->count();
        $data['colors'][] = '#2CB100';

        $data['labels'] = [
            __('modules.dashboard.estimateDraft'),
            __('modules.dashboard.estimateNotSent'),
            __('modules.dashboard.estimateSent'),
            __('modules.dashboard.estimateDeclined'),
            __('modules.dashboard.estimateExpired'),
            __('modules.dashboard.estimateAccepted')
        ];

        return $data;
    }

    /**
     * Prepare chart data for proposals (status distribution).
     */
    public function proposalOverviewChartData($startDate, $endDate)
    {
        $data['values'] = [];
        $data['colors'] = [];

        $allProposal = Proposal::whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate])->get();

        $data['values'][] = $allProposal->where('status', 'waiting')->count();
        $data['colors'][] = '#FCBD01';

        $data['values'][] = $allProposal->where('status', 'declined')->count();
        $data['colors'][] = '#D30000';

        // NOTE: Bug in original code (`=` instead of `==`). Fixed here.
        $data['values'][] = $allProposal->filter(fn($v) =>
            $v->status == 'waiting' && $v->valid_till->lessThan(now())
        )->count();
        $data['colors'][] = '#99A5B5';

        $data['values'][] = $allProposal->where('status', 'accepted')->count();
        $data['colors'][] = '#2CB100';

        $data['values'][] = $allProposal->where('invoice_convert', 1)->count();
        $data['colors'][] = '#1d82f5';

        $data['labels'] = [
            __('modules.dashboard.proposalWaiting'),
            __('modules.dashboard.proposalDeclined'),
            __('modules.dashboard.proposalExpired'),
            __('modules.dashboard.proposalAccepted'),
            __('modules.dashboard.proposalConverted')
        ];

        return $data;
    }

    /**
     * Prepare earnings chart grouped by projects.
     */
    public function projectEarningChartData($startDate, $endDate)
    {
        // Payments linked directly to projects
        $paymentsModal = Payment::whereBetween(DB::raw('DATE(payments.`paid_on`)'), [$startDate, $endDate]);
        $projects = clone $paymentsModal;

        $projects->join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->join('projects', 'projects.id', '=', 'payments.project_id')
            ->where('payments.status', 'complete')
            ->orderBy('payments.paid_on', 'ASC')
            ->select(
                'payments.amount as total',
                'payments.exchange_rate',
                'currencies.currency_code',
                'currencies.is_cryptocurrency',
                'currencies.usd_price',
                'projects.project_name'
            );

        // Payments linked to invoices (and projects)
        $invoices = clone $paymentsModal;
        $invoices = $invoices->join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->join('projects', 'projects.id', '=', 'invoices.project_id')
            ->where('payments.status', 'complete')
            ->orderBy('payments.paid_on', 'ASC')
            ->select(
                'payments.amount as total',
                'payments.exchange_rate',
                'currencies.currency_code',
                'currencies.is_cryptocurrency',
                'currencies.usd_price',
                'projects.project_name'
            )
            ->union($projects)
            ->get();

        $earningsByProjects = [];

        foreach ($invoices as $invoice) {
            if (!array_key_exists($invoice->project_name, $earningsByProjects)) {
                $earningsByProjects[$invoice->project_name] = 0;
            }

            // Convert project earnings into company currency
            if ($invoice->currency_code != $this->company->currency->currency_code && $invoice->exchange_rate != 0) {
                if ($invoice->is_cryptocurrency == 'yes') {
                    $usdTotal = ($invoice->total * $invoice->usd_price);
                    $earningsByProjects[$invoice->project_name] += round(floor(floatval($usdTotal) * floatval($invoice->exchange_rate)), 2);
                } else {
                    $earningsByProjects[$invoice->project_name] += round((floatval($invoice->total) * floatval($invoice->exchange_rate)), 2);
                }
            } else {
                $earningsByProjects[$invoice->project_name] += round($invoice->total, 2);
            }
        }

        $data['labels'] = array_keys($earningsByProjects);
        $data['values'] = array_values($earningsByProjects);
        $data['colors'] = [$this->appTheme->header_color];
        $data['name'] = __('app.earnings');

        return $data;
    }
}
