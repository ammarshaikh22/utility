<?php

namespace App\Http\Controllers;

use App\DataTables\ExpenseCategoryReportDataTable;
use Illuminate\Http\Request;
use App\DataTables\ExpenseReportDataTable;
use App\Helper\Reply;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpensesCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExpenseReportController extends AccountBaseController
{

    /**
     * Constructor function.
     * Sets up page titles and initializes parent controller properties.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.expenseReport';
        $this->categoryTitle = 'modules.expenseCategory.expenseCategoryReport';
    }

    /**
     * Display the main Expense Report page.
     * Loads filters like dates, currencies, projects, employees, and categories.
     *
     * @param ExpenseReportDataTable $dataTable
     * @return mixed
     */
    public function index(ExpenseReportDataTable $dataTable)
    {
        abort_403(user()->permission('view_expense_report') != 'all');

        $this->fromDate = now($this->company->timezone)->startOfMonth();
        $this->toDate = now($this->company->timezone);
        $this->currencies = Currency::all();
        $this->currentCurrencyId = $this->company->currency_id;

        $this->projects = Project::allProjects();
        $this->employees = User::withRole('employee')->get();
        $this->categories = ExpensesCategory::get();

        return $dataTable->render('reports.expense.index', $this->data);
    }

    /**
     * Fetch and process expense report data for chart visualization.
     * Includes both line chart (daily expenses) and bar chart (category-wise summary).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function expenseChartData(Request $request)
    {
        // -----------------------------------
        // EXPENSE REPORT (LINE CHART) START
        // -----------------------------------

        $startDate = ($request->startDate == null) ? null : now($this->company->timezone)->startOfMonth()->toDateString();
        $endDate = ($request->endDate == null) ? null : now($this->company->timezone)->toDateString();

        // Query only approved expenses
        $expenses = Expense::where('status', 'approved');

        // Filter by start date
        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            $expenses = $expenses->where(DB::raw('DATE(`purchase_date`)'), '>=', $startDate);
        }

        // Filter by end date
        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            $expenses = $expenses->where(DB::raw('DATE(`purchase_date`)'), '<=', $endDate);
        }

        // Filter by category
        if ($request->categoryID != 'all' && !is_null($request->categoryID)) {
            $expenses = $expenses->where('category_id', '=', $request->categoryID);
        }

        // Filter by project
        if ($request->projectID != 'all' && !is_null($request->projectID)) {
            $expenses = $expenses->where('project_id', '=', $request->projectID);
        }

        // Filter by employee
        if ($request->employeeID != 'all' && !is_null($request->employeeID)) {
            $employeeID = $request->employeeID;
            $expenses = $expenses->where(function ($query) use ($employeeID) {
                $query->where('user_id', $employeeID);
            });
        }

        // Get filtered expense data
        $expenses = $expenses->orderBy('purchase_date', 'ASC')
            ->get([
                DB::raw('DATE_FORMAT(purchase_date,"%d-%M-%y") as date'),
                DB::raw('YEAR(purchase_date) year, MONTH(purchase_date) month'),
                'price',
                'user_id',
                'project_id',
                'currency_id',
                'exchange_rate',
                'default_currency_id',
                'category_id',
            ]);

        // Prepare daily expense totals
        $prices = array();
        foreach ($expenses as $expense) {
            if (!isset($prices[$expense->date])) {
                $prices[$expense->date] = 0;
            }
            $prices[$expense->date] += $expense->default_currency_price;
        }

        // Create graph data (date vs total expense)
        $dates = array_keys($prices);
        $graphData = array();

        foreach ($dates as $date) {
            $graphData[] = [
                'date' => $date,
                'total' => isset($prices[$date]) ? round($prices[$date], 2) : 0,
            ];
        }

        // Sort data chronologically
        usort($graphData, function ($a, $b) {
            $t1 = strtotime($a['date']);
            $t2 = strtotime($b['date']);
            return $t1 - $t2;
        });

        // Prepare chart data arrays
        $graphData = collect($graphData);
        $data['labels'] = $graphData->pluck('date')->toArray();
        $data['values'] = $graphData->pluck('total')->toArray();
        $totalExpense = $graphData->sum('total');
        $data['colors'] = [$this->appTheme->header_color];
        $data['name'] = __('modules.dashboard.totalExpenses');
        $this->chartData = $data;

        // -----------------------------------
        // EXPENSE REPORT (LINE CHART) END
        // -----------------------------------


        // -----------------------------------
        // EXPENSE CATEGORY REPORT (BAR CHART) START
        // -----------------------------------

        $startDate = ($request->startDate == null) ? null : now($this->company->timezone)->startOfMonth()->toDateString();
        $endDate = ($request->endDate == null) ? null : now($this->company->timezone)->toDateString();

        // Get unique category IDs from approved expenses
        $expenseCategoryId = ExpensesCategory::join('expenses', 'expenses_category.id', '=', 'expenses.category_id')
            ->where('expenses.status', 'approved')
            ->where('expenses.category_id', '!=', null);

        // Apply filters to category data
        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            $expenses = $expenseCategoryId->where(DB::raw('DATE(expenses.`purchase_date`)'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            $expenses = $expenseCategoryId->where(DB::raw('DATE(expenses.`purchase_date`)'), '<=', $endDate);
        }

        if ($request->employeeID != 'all' && !is_null($request->employeeID)) {
            $expenseCategoryId = $expenseCategoryId->where('expenses.user_id', $request->employeeID);
        }

        if ($request->projectID != 'all' && !is_null($request->projectID)) {
            $expenseCategoryId = $expenseCategoryId->where('expenses.project_id', $request->projectID);
        }

        // Collect category IDs and fetch category details
        $expenseCategoryId = $expenseCategoryId->distinct('expenses.category_id')
            ->selectRaw('expenses.category_id as id')
            ->pluck('id')
            ->toArray();

        $categories = ExpensesCategory::whereIn('id', $expenseCategoryId)->get();

        // Apply specific category filter
        if ($request->categoryID != 'all' && !is_null($request->categoryID)) {
            $categories = $categories->where('id', $request->categoryID);
        }

        // Prepare bar chart data
        $barData['labels'] = $categories->pluck('category_name');
        $barData['name'] = __('modules.reports.totalCategories');
        $barData['colors'] = [$this->appTheme->header_color];
        $barData['values'] = [];

        foreach ($categories as $category) {
            /** @phpstan-ignore-next-line */
            $category_id = isset($category->id) ? $category->id : $category->category_id;

            if ($startDate && $endDate != null) {
                $barData['values'][] = Expense::where('category_id', $category_id)
                    ->whereBetween(DB::raw('DATE(`purchase_date`)'), [$startDate, $endDate])
                    ->count();
            } else {
                $barData['values'][] = Expense::where('category_id', $category_id)->count();
            }
        }

        $this->barChartData = $barData;

        // -----------------------------------
        // EXPENSE CATEGORY REPORT (BAR CHART) END
        // -----------------------------------

        // Render both chart views and return response
        $html = view('reports.expense.chart', $this->data)->render(); // Expense line chart view
        $html2 = view('reports.expense.bar_chart', $this->data)->render(); // Category bar chart view

        return Reply::dataOnly([
            'status' => 'success',
            'html' => $html,
            'html2' => $html2,
            'title' => $this->pageTitle,
            'totalExpenses' => currency_format($totalExpense, company()->currency_id)
        ]);
    }

    /**
     * Display the Expense Category Report page.
     * Shows report based on expense categories.
     *
     * @return mixed
     */
    public function expenseCategoryReport()
    {
        abort_403(user()->permission('view_expense_report') != 'all');
        $dataTable = new ExpenseCategoryReportDataTable();

        $this->fromDate = now($this->company->timezone)->startOfMonth();
        $this->toDate = now($this->company->timezone);
        $this->categories = ExpensesCategory::get();

        return $dataTable->render('reports.expense.expense-category-report', $this->data);
    }

}
