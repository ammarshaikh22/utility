<?php

namespace App\Http\Controllers;

use App\DataTables\ExpensesDataTable;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Http\Requests\Expenses\StoreExpense;
use App\Imports\ExpenseImport;
use App\Jobs\ImportExpenseJob;
use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpensesCategory;
use App\Models\ExpensesCategoryRole;
use App\Models\Project;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Traits\ImportExcel;
use Illuminate\Http\Request;

/**
 * Controller for managing expenses, including CRUD operations, status changes, and import functionality.
 */
class ExpenseController extends AccountBaseController
{
    use ImportExcel;

    /**
     * Initializes the controller, setting the page title and applying middleware to restrict access to users with the 'expenses' module.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.expenses';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('expenses', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Displays the list of expenses with filtering and pagination.
     *
     * @param ExpensesDataTable $dataTable The data table instance for rendering expenses.
     * @return \Illuminate\View\View Returns the expenses index view or data table response.
     */
    public function index(ExpensesDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_expenses');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->employees = User::allEmployees(null, true);
            $this->projects = Project::allProjects();
            $this->categories = ExpenseCategoryController::getCategoryByCurrentRole();
        }

        return $dataTable->render('expenses.index', $this->data);
    }

    /**
     * Changes the status of a specific expense.
     *
     * @param Request $request The HTTP request containing the expense ID and new status.
     * @return \App\Helper\Reply Returns a JSON response indicating success.
     */
    public function changeStatus(Request $request)
    {
        abort_403(user()->permission('approve_expenses') != 'all');

        $expenseId = $request->expenseId;
        $status = $request->status;
        $expense = Expense::findOrFail($expenseId);
        $expense->status = $status;
        $expense->save();
        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Displays the details of a specific expense.
     *
     * @param int $id The ID of the expense to display.
     * @return \Illuminate\View\View Returns the expense details view or AJAX response.
     */
    public function show($id)
    {
        $this->expense = Expense::with(['user', 'project', 'category', 'transactions' => function($q){
            $q->orderByDesc('id')->limit(1);
        }, 'transactions.bankAccount'])->findOrFail($id)->withCustomFields();

        $this->viewPermission = user()->permission('view_expenses');
        $viewProjectPermission = user()->permission('view_project_expenses');
        $this->editExpensePermission = user()->permission('edit_expenses');
        $this->deleteExpensePermission = user()->permission('delete_expenses');

        abort_403(!($this->viewPermission == 'all'
        || ($this->viewPermission == 'added' && $this->expense->added_by == user()->id)
        || ($viewProjectPermission == 'owned' || $this->expense->user_id == user()->id)));

        $getCustomFieldGroupsWithFields = $this->expense->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->pageTitle = $this->expense->item_name;
        $this->view = 'expenses.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('expenses.show', $this->data);
    }

    /**
     * Displays the form for creating a new expense.
     *
     * @return \Illuminate\View\View Returns the create expense view or AJAX response.
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_expenses');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->currencies = Currency::all();
        $this->categories = ExpenseCategoryController::getCategoryByCurrentRole();
        $this->linkExpensePermission = user()->permission('link_expense_bank_account');
        $this->viewBankAccountPermission = user()->permission('view_bankaccount');

        $bankAccounts = BankAccount::where('status', 1)->where('currency_id', company()->currency_id);

        if($this->viewBankAccountPermission == 'added'){
            $bankAccounts = $bankAccounts->where('added_by', user()->id);
        }

        $bankAccounts = $bankAccounts->get();
        $this->bankDetails = $bankAccounts;
        $this->companyCurrency = Currency::where('id', company()->currency_id)->first();

        // Get only current login employee projects
        if ($this->addPermission == 'added') {
            $this->projects = Project::where('added_by', user()->id)->orWhereHas('projectMembers', function ($query) {
                $query->where('user_id', user()->id);
            })->get();
        } else {
            $this->projects = Project::all();
        }

        $this->pageTitle = __('modules.expenses.addExpense');
        $this->projectId = request('project_id') ? request('project_id') : null;

        if (!is_null($this->projectId)) {
            $this->project = Project::with('projectMembers')->where('id', $this->projectId)->first();
            $this->projectName = $this->project->project_name;
            $this->employees = $this->project->projectMembers;
        } else {
            $this->employees = User::allEmployees(null, true);
        }

        $expense = new Expense();

        $getCustomFieldGroupsWithFields = $expense->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'expenses.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('expenses.show', $this->data);
    }

    /**
     * Stores a new expense with associated details and optional file upload.
     *
     * @param StoreExpense $request The validated HTTP request containing expense data.
     * @return \App\Helper\Reply Returns a JSON response with success message and redirect URL.
     */
    public function store(StoreExpense $request)
    {
        $currencySetting = Currency::findOrFail($request->currency_id);

        $userRole = session('user_roles');
        $expense = new Expense();
        $expense->item_name = $request->item_name;
        $expense->purchase_date = companyToYmd($request->purchase_date);
        $expense->purchase_from = $request->purchase_from;
        $expense->price = round($request->price, $currencySetting->no_of_decimal);
        $expense->currency_id = $request->currency_id;
        $expense->category_id = $request->category_id;
        $expense->user_id = $request->user_id;
        $expense->default_currency_id = company()->currency_id;
        $expense->exchange_rate = $request->exchange_rate;
        $expense->description = trim_editor($request->description);

        if ($userRole[0] == 'admin') {
            $expense->status = 'approved';
            $expense->approver_id = user()->id;
        }

        if ($request->has('status')) {
            $expense->status = $request->status;
            $expense->approver_id = user()->id;
        }

        if ($request->has('project_id') && $request->project_id != '0') {
            $expense->project_id = $request->project_id;
        }

        if ($request->hasFile('bill')) {
            $filename = Files::uploadLocalOrS3($request->bill, Expense::FILE_PATH);
            $expense->bill = $filename;
        }

        $expense->bank_account_id = $request->bank_account_id;

        $expense->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $expense->updateCustomFieldData($request->custom_fields_data);
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('expenses.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Displays the form for editing an existing expense.
     *
     * @param int $id The ID of the expense to edit.
     * @return \Illuminate\View\View Returns the edit expense view or AJAX response.
     */
    public function edit($id)
    {
        $this->expense = Expense::findOrFail($id)->withCustomFields();
        $this->editPermission = user()->permission('edit_expenses');

        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->expense->added_by == user()->id)));

        $this->currencies = Currency::all();
        $this->categories = ExpenseCategoryController::getCategoryByCurrentRole();
        $this->employees = User::allEmployees(null, false);
        $this->pageTitle = __('modules.expenses.updateExpense');
        $this->linkExpensePermission = user()->permission('link_expense_bank_account');
        $this->viewBankAccountPermission = user()->permission('view_bankaccount');

        $bankAccounts = BankAccount::where('status', 1)->where('currency_id', $this->expense->currency_id);

        if($this->viewBankAccountPermission == 'added'){
            $bankAccounts = $bankAccounts->where('added_by', user()->id);
        }

        $bankAccounts = $bankAccounts->get();
        $this->bankDetails = $bankAccounts;

        $userId = $this->expense->user_id;

        if (!is_null($userId)) {
            $this->projects = Project::with('members')->whereHas('members', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->get();
        }
        else {
            $this->projects = Project::get();
        }

        $this->companyCurrency = Currency::where('id', company()->currency_id)->first();

        $expense = new Expense();

        $getCustomFieldGroupsWithFields = $expense->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'expenses.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('expenses.show', $this->data);
    }

    /**
     * Updates an existing expense with new details and optional file upload.
     *
     * @param StoreExpense $request The validated HTTP request containing updated expense data.
     * @param int $id The ID of the expense to update.
     * @return \App\Helper\Reply Returns a JSON response with success message and redirect URL.
     */
    public function update(StoreExpense $request, $id)
    {
        $currencySetting = Currency::findOrFail($request->currency_id);

        $expense = Expense::findOrFail($id);
        $expense->item_name = $request->item_name;
        $expense->purchase_date = companyToYmd($request->purchase_date);
        $expense->purchase_from = $request->purchase_from;
        $expense->price = round($request->price, $currencySetting->no_of_decimal);
        $expense->currency_id = $request->currency_id;
        $expense->user_id = $request->user_id;
        $expense->category_id = $request->category_id;
        $expense->default_currency_id = company()->currency_id;
        $expense->exchange_rate = $request->exchange_rate;
        $expense->description = trim_editor($request->description);

        $expense->project_id = ($request->project_id > 0) ? $request->project_id : null;

        if ($request->bill_delete == 'yes') {
            Files::deleteFile($expense->bill, Expense::FILE_PATH);
            $expense->bill = null;
        }

        if ($request->hasFile('bill')) {
            Files::deleteFile($expense->bill, Expense::FILE_PATH);

            $filename = Files::uploadLocalOrS3($request->bill, Expense::FILE_PATH);
            $expense->bill = $filename;
        }

        if ($request->has('status')) {
            $expense->status = $request->status;
        }

        $expense->bank_account_id = $request->bank_account_id;
        $expense->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $expense->updateCustomFieldData($request->custom_fields_data);
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('expenses.index')]);
    }

    /**
     * Deletes a specific expense.
     *
     * @param int $id The ID of the expense to delete.
     * @return \App\Helper\Reply Returns a JSON response indicating success.
     */
    public function destroy($id)
    {
        $this->expense = Expense::findOrFail($id);
        $this->deletePermission = user()->permission('delete_expenses');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $this->expense->added_by == user()->id)));

        Expense::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Applies bulk actions (delete or change status) to selected expenses.
     *
     * @param Request $request The HTTP request containing action type and row IDs.
     * @return \App\Helper\Reply Returns a JSON response indicating success or failure.
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);
                return Reply::success(__('messages.deleteSuccess'));
        case 'change-status':
            $this->changeBulkStatus($request);
                return Reply::success(__('messages.updateSuccess'));
        default:
                return Reply::error(__('messages.selectAction'));
        }
    }

    /**
     * Deletes multiple expenses based on provided IDs.
     *
     * @param Request $request The HTTP request containing row IDs to delete.
     * @return void
     */
    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_employees') != 'all');

        // Did this to call observer
        foreach (Expense::withoutGlobalScope(ActiveScope::class)->whereIn('id', explode(',', $request->row_ids))->get() as $delete) {
            $delete->delete();
        }
    }

    /**
     * Changes the status of multiple expenses in bulk.
     *
     * @param Request $request The HTTP request containing status and row IDs.
     * @return void
     */
    protected function changeBulkStatus($request)
    {
        abort_403(user()->permission('edit_employees') != 'all');

        $expenses = Expense::withoutGlobalScope(ActiveScope::class)->whereIn('id', explode(',', $request->row_ids))->get();

        $expenses->each(function ($expense) use ($request) {
            $expense->status = $request->status;
            $expense->save();
        });
    }

    /**
     * Retrieves projects and categories associated with a specific employee.
     *
     * @param Request $request The HTTP request containing user ID and category ID.
     * @return \App\Helper\Reply Returns a JSON response with project and category options.
     */
    protected function getEmployeeProjects(Request $request)
    {
        // Get employee category
        if (!is_null($request->userId)) {
            $categories = ExpensesCategory::with('roles')->whereHas('roles', function($q) use ($request) {
                $user = User::withoutGlobalScope(ActiveScope::class)->findOrFail($request->userId);

                $roleId = (count($user->role) > 1) ? $user->role[1]->role_id : $user->role[0]->role_id;
                $q->where('role_id', $roleId);
            })->get();
        }
        else {
            $categories = ExpensesCategory::get();
        }

        if($categories) {
            foreach ($categories as $category) {
                $selected = $category->id == $request->categoryId ? 'selected' : '';
                $categories .= '<option value="' . $category->id . '"'.$selected.'>' . $category->category_name . '</option>';
            }
        }

        // Get employee project
        if (!is_null($request->userId)) {
            $projects = Project::with('members')->whereHas('members', function ($q) use ($request) {
                $q->where('user_id', $request->userId);
            })->get();
        }
        else if(user()->permission('add_expenses') == 'all' && is_null($request->userId))
        {
            $projects = [];
        }
        else {
            $projects = Project::get();
        }

        $data = null;

        if ($projects) {
            foreach ($projects as $project) {
                $data .= '<option data-currency-id="'. $project->currency_id .'" value="' . $project->id . '">' . $project->project_name . '</option>';
            }
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $data, 'category' => $categories]);
    }

    /**
     * Retrieves employees associated with a specific expense category.
     *
     * @param Request $request The HTTP request containing category ID and user ID.
     * @return \App\Helper\Reply Returns a JSON response with employee options.
     */
    protected function getCategoryEmployee(Request $request)
    {
        $expenseCategory = ExpensesCategoryRole::where('expenses_category_id', $request->categoryId)->get();
        $roleId = [];
        $managers = [];
        $employees = [];

        foreach($expenseCategory as $category) {
            array_push($roleId, $category->role_id);
        }

        if (count($roleId ) == 1 && $roleId != null) {
            $users = User::whereHas(
                'role', function($q)  use ($roleId) {
                    $q->whereIn('role_id', $roleId);
                }
            )->get();

            foreach ($users as $user) {
                ($user->hasRole('Manager')) ? array_push($managers, $user) : array_push($employees, $user);
            }
        }
        else {
            $employees = User::allEmployees(null, false);
        }

        $data = null;

        if ($employees) {
            foreach ($employees as $employee) {
                if($employee->status == 'active' || $employee->id == $request->userId){
                    $data .= '<option ';

                    $content = ($employee->status == 'deactive') ? "<span class='badge badge-pill badge-danger border align-center ml-2 px-2'>Inactive</span>" : '';
                    $selected = $employee->id == $request->userId ? 'selected' : '';
                    $itsYou = $employee->id == user()->id ? "<span class='ml-2 badge badge-secondary pr-1'>". __('app.itsYou') .'</span>' : '';

                    $data .= 'data-content="<div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=\'' . $employee->image_url . '\' ></div> '.$employee->name.$itsYou.$content.'"
                    value="' . $employee->id . '"'.$selected.'>'.$employee->name.'</option>';
                }
            }
        }
        else {
            foreach ($managers as $manager) {
                $data .= '<option ';

                $selected = $manager->id == $request->userId ? 'selected' : '';
                $itsYou = $manager->id == user()->id ? "<span class='ml-2 badge badge-secondary pr-1'>" . __('app.itsYou') . '</span>' : '';
                $data .= 'data-content="<div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=\'' . $manager->image_url . '\' ></div> '.$manager->name.'"
                value="' . $manager->id . '"'.$selected.'>'.$manager->name.$itsYou.'</option>';
            }
        }

        return Reply::dataOnly(['status' => 'success', 'employees' => $data]);
    }

    /**
     * Displays the form for importing expenses via Excel.
     *
     * @return \Illuminate\View\View Returns the import expense view or AJAX response.
     */
    public function import()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.menu.expenses');

        $addPermission = user()->permission('add_expenses');
        abort_403(!in_array($addPermission, ['all', 'added']));

        $this->view = 'expenses.ajax.import';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('expenses.show', $this->data);
    }

    /**
     * Processes the uploaded Excel file for importing expenses.
     *
     * @param ImportRequest $request The validated HTTP request containing the Excel file.
     * @return \App\Helper\Reply Returns a JSON response with success message and import progress view.
     */
    public function importStore(ImportRequest $request)
    {
        $rvalue = $this->importFileProcess($request, ExpenseImport::class);

        if($rvalue == 'abort'){
            return Reply::error(__('messages.abortAction'));
        }

        $view = view('expenses.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    /**
     * Handles the background processing of the expense import job.
     *
     * @param ImportProcessRequest $request The validated HTTP request containing import process data.
     * @return \App\Helper\Reply Returns a JSON response with success message and batch ID.
     */
    public function importProcess(ImportProcessRequest $request)
    {
        $batch = $this->importJobProcess($request, ExpenseImport::class, ImportExpenseJob::class);

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }
}