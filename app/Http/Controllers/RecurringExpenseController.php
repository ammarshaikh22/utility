<?php

namespace App\Http\Controllers;

use App\DataTables\ExpensesDataTable;
use App\DataTables\RecurringExpensesDataTable;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Expenses\StoreRecurringExpense;
use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseRecurring;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Class RecurringExpenseController
 * ---------------------------------
 * Manages recurring expense creation, editing, and display.
 * Handles linked projects, users, currencies, and expense records.
 *
 * @package App\Http\Controllers
 */
class RecurringExpenseController extends AccountBaseController
{
    /**
     * Constructor
     * --------------------------
     * Initializes default page title and restricts access to users
     * who have the 'expenses' module enabled.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.expensesRecurring';

        // Ensure user has access to the 'expenses' module
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('expenses', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Display list of recurring expenses.
     *
     * @param RecurringExpensesDataTable $dataTable
     * @return mixed
     */
    public function index(RecurringExpensesDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_expenses');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        // Load dropdown data only on non-AJAX requests
        if (!request()->ajax()) {
            $this->employees = User::allEmployees();
            $this->projects = Project::allProjects();
            $this->categories = ExpenseCategoryController::getCategoryByCurrentRole();
        }

        return $dataTable->render('recurring-expenses.index', $this->data);
    }

    /**
     * Show the form for creating a new recurring expense.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('manage_recurring_expense');
        abort_403(!in_array($this->addPermission, ['all']));

        // Prepare base data
        $this->currencies = Currency::all();
        $this->categories = ExpenseCategoryController::getCategoryByCurrentRole();
        $this->projects = Project::all();
        $this->pageTitle = __('modules.expensesRecurring.addExpense');
        $this->projectId = request('project_id') ?: null;

        // Determine employees (based on project or all)
        if (!is_null($this->projectId)) {
            $employees = Project::with('projectMembers')->where('id', $this->projectId)->first();
            $this->employees = $employees->projectMembers;
        } else {
            $this->employees = User::allEmployees();
        }

        // Load custom fields
        $expense = new Expense();
        $getCustomFieldGroupsWithFields = $expense->getCustomFieldGroupsWithFields();
        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        // Load permissions and bank account options
        $this->linkExpensePermission = user()->permission('link_expense_bank_account');
        $this->viewBankAccountPermission = user()->permission('view_bankaccount');

        $bankAccounts = BankAccount::where('status', 1)->where('currency_id', company()->currency_id);
        if ($this->viewBankAccountPermission == 'added') {
            $bankAccounts = $bankAccounts->where('added_by', user()->id);
        }
        $this->bankDetails = $bankAccounts->get();

        // Handle AJAX vs full view
        if (request()->ajax()) {
            $html = view('recurring-expenses.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'recurring-expenses.ajax.create';
        return view('expenses.show', $this->data);
    }

    /**
     * Store a newly created recurring expense in the database.
     *
     * @param StoreRecurringExpense $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRecurringExpense $request)
    {
        // Create a new recurring expense record
        $expenseRecurring = new ExpenseRecurring();
        $expenseRecurring->item_name = $request->item_name;
        $expenseRecurring->price = round($request->price, 2);
        $expenseRecurring->currency_id = $request->currency_id;
        $expenseRecurring->category_id = $request->category_id;
        $expenseRecurring->user_id = $request->user_id;
        $expenseRecurring->status = 'active';
        $expenseRecurring->rotation = $request->rotation;
        $expenseRecurring->billing_cycle = $request->billing_cycle > 0 ? $request->billing_cycle : null;
        $expenseRecurring->unlimited_recurring = $request->billing_cycle < 0 ? 1 : 0;
        $expenseRecurring->description = trim_editor($request->description);
        $expenseRecurring->created_by = $this->user->id;
        $expenseRecurring->purchase_from = $request->purchase_from;
        $expenseRecurring->issue_date = !is_null($request->issue_date)
            ? companyToYmd($request->issue_date)
            : now()->format('Y-m-d');
        $expenseRecurring->bank_account_id = $request->bank_account_id;
        $expenseRecurring->immediate_expense = $request->immediate_expense ? 1 : 0;

        if ($request->project_id > 0) {
            $expenseRecurring->project_id = $request->project_id;
        }

        // Upload attached bill file
        if ($request->hasFile('bill')) {
            $filename = Files::uploadLocalOrS3($request->bill, Expense::FILE_PATH);
            $expenseRecurring->bill = $filename;
        }

        $expenseRecurring->save();

        /**
         * If immediate_expense = true,
         * create an actual Expense record right away.
         */
        if ($request->immediate_expense) {
            $currency = Currency::find($request->currency_id);
            $expense = new Expense();
            $expense->expenses_recurring_id = $expenseRecurring->id;
            $expense->category_id = $request->category_id;
            $expense->project_id = $request->project_id;
            $expense->currency_id = $request->currency_id;
            $expense->user_id = $request->user_id;
            $expense->created_by = $expenseRecurring->created_by;
            $expense->item_name = $request->item_name;
            $expense->description = $request->description;
            $expense->price = $request->price;
            $expense->default_currency_id = company()->currency_id;
            $expense->exchange_rate = $currency->exchange_rate;
            $expense->purchase_from = $request->purchase_from;
            $expense->purchase_date = now()->format('Y-m-d');
            $expense->bank_account_id = $expenseRecurring->bank_account_id;
            $expense->status = 'approved';
            $expense->save();

            if ($request->custom_fields_data) {
                $expense->updateCustomFieldData($request->custom_fields_data);
            }
        }

        // Redirect to appropriate page after save
        $redirectUrl = urldecode($request->redirect_url) ?: route('recurring-expenses.show', $expenseRecurring->id);

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Display a recurring expense and its related records.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->expense = ExpenseRecurring::with('recurrings')->findOrFail($id);
        $this->exp = Expense::where('expenses_recurring_id', $id)->first();

        if ($this->exp) {
            $this->exp = $this->exp->withCustomFields();
            $getCustomFieldGroupsWithFields = $this->exp->getCustomFieldGroupsWithFields();
            if ($getCustomFieldGroupsWithFields) {
                $this->fields = $getCustomFieldGroupsWithFields->fields;
            }
        }

        $this->daysOfWeek = [
            '1' => 'sunday', '2' => 'monday', '3' => 'tuesday',
            '4' => 'wednesday', '5' => 'thursday', '6' => 'friday', '7' => 'saturday'
        ];

        $tab = request('tab');
        $this->activeTab = $tab ?: 'overview';

        switch ($tab) {
            case 'expenses':
                return $this->expenses($id);
            default:
                $this->view = 'recurring-expenses.ajax.show';
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('recurring-expenses.show', $this->data);
    }

    /**
     * Show edit form for a specific recurring expense.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->addPermission = user()->permission('manage_recurring_expense');
        abort_403(!in_array($this->addPermission, ['all']));

        $this->expense = ExpenseRecurring::findOrFail($id);
        $this->currencies = Currency::all();
        $this->categories = ExpenseCategoryController::getCategoryByCurrentRole();
        $this->pageTitle = __('modules.expensesRecurring.addExpense');

        // Load related permissions and custom fields
        $this->linkExpensePermission = user()->permission('link_expense_bank_account');
        $this->viewBankAccountPermission = user()->permission('view_bankaccount');
        $this->exp = Expense::where('expenses_recurring_id', $id)->first();

        if ($this->exp) {
            $this->exp = $this->exp->withCustomFields();
            $getCustomFieldGroupsWithFields = $this->exp->getCustomFieldGroupsWithFields();
            if ($getCustomFieldGroupsWithFields) {
                $this->fields = $getCustomFieldGroupsWithFields->fields;
            }
        }

        // Filter bank accounts based on user permission
        $bankAccounts = BankAccount::where('status', 1)
            ->where('currency_id', $this->expense->currency_id);

        if ($this->viewBankAccountPermission == 'added') {
            $bankAccounts = $bankAccounts->where('added_by', user()->id);
        }

        $this->bankDetails = $bankAccounts->get();

        // Load project and employee options
        $userId = $this->expense->user_id;
        $this->projects = $userId
            ? Project::with('members')->whereHas('members', fn($q) => $q->where('user_id', $userId))->get()
            : Project::get();

        $this->employees = $this->projectId
            ? Project::with('projectMembers')->find($this->projectId)->projectMembers
            : User::allEmployees();

        $this->view = 'recurring-expenses.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('expenses.show', $this->data);
    }

    /**
     * Update the specified recurring expense in storage.
     *
     * @param StoreRecurringExpense $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreRecurringExpense $request, $id)
    {
        $expense = ExpenseRecurring::findOrFail($id);

        // If no expenses yet, allow full edit
        if ($request->expense_count == 0) {
            $expense->item_name = $request->item_name;
            $expense->price = round($request->price, 2);
            $expense->currency_id = $request->currency_id;
            $expense->category_id = $request->category_id;
            $expense->user_id = $request->user_id;
            $expense->rotation = $request->rotation;
            $expense->billing_cycle = $request->billing_cycle > 0 ? $request->billing_cycle : null;
            $expense->unlimited_recurring = $request->billing_cycle < 0 ? 1 : 0;
            $expense->description = trim_editor($request->description);
            $expense->purchase_from = $request->purchase_from;
            $expense->bank_account_id = $request->bank_account_id;

            if (!is_null($request->issue_date)) {
                $expense->issue_date = companyToYmd($request->issue_date);
            }

            if ($request->project_id > 0) {
                $expense->project_id = $request->project_id;
            }

            if ($request->hasFile('bill')) {
                $filename = Files::uploadLocalOrS3($request->bill, Expense::FILE_PATH);
                $expense->bill = $filename;
            }

            $expense->save();
        } else {
            if (request()->has('status')) {
                $expense->status = $request->status;
            }
            $expense->save();
        }

        // Update related custom field data
        $this->exp = Expense::where('expenses_recurring_id', $id)->first();
        if ($this->exp) {
            $this->exp = $this->exp->withCustomFields();
            if ($request->custom_fields_data) {
                $this->exp->updateCustomFieldData($request->custom_fields_data);
            }
        }

        $redirectUrl = urldecode($request->redirect_url) ?: route('recurring-expenses.show', $expense->id);
        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Delete a recurring expense.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->expense = ExpenseRecurring::findOrFail($id);
        $this->deletePermission = user()->permission('delete_expenses');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $this->expense->added_by == user()->id)));

        ExpenseRecurring::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Display actual expenses linked to a recurring expense.
     *
     * @param int $recurringID
     * @return mixed
     */
    public function expenses($recurringID)
    {
        $dataTable = new ExpensesDataTable();
        $viewPermission = user()->permission('view_expenses');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->recurringID = $recurringID;
        $this->expense = ExpenseRecurring::findOrFail($recurringID);
        $this->activeTab = request('tab') ?: 'overview';
        $this->view = 'recurring-expenses.ajax.expenses';

        return $dataTable->render('recurring-expenses.show', $this->data);
    }

    /**
     * Change recurring expense status (active/inactive).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request)
    {
        $expense = ExpenseRecurring::findOrFail($request->expenseId);
        $expense->status = $request->status;
        $expense->save();

        return Reply::success(__('messages.updateSuccess'));
    }
}
