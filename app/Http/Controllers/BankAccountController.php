<?php

namespace App\Http\Controllers;

use App\DataTables\BankAccountDataTable;
use App\DataTables\BankTransactionDataTable;
use App\Http\Requests\BankAccount\StoreAccount;
use App\Http\Requests\BankAccount\StoreTransaction;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankAccountController extends AccountBaseController
{

    public function __construct()
    {

        parent::__construct();
        $this->pageTitle = __('app.menu.bankaccount');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('bankaccount', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Constructor
     *
     * Purpose: Initialize controller defaults and middleware to ensure the user has access to bank account module.
     * Inputs: none
     * Outputs: none (controller state initialized)
     */

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(BankAccountDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_bankaccount');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $bankDetails = BankAccount::select('*');

        if ($viewPermission == 'added') {
            $bankDetails = $bankDetails->where('added_by', user()->id);
        }

        $bankDetails = $bankDetails->get();
        $this->bankAccounts = $bankDetails;

        return $dataTable->render('bank-account.index', $this->data);

    }

    /**
     * List bank accounts.
     *
     * Purpose: Prepare bank accounts for listing and render DataTable view.
     * Inputs: BankAccountDataTable instance
     * Outputs: Rendered view for bank-account.index or AJAX datatable response
     * Side effects: Aborts with 403 if user lacks view permission
     */

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_bankaccount');
        abort_403(!in_array($this->addPermission, ['all']));

        $this->currencies = Currency::all();

        $this->view = 'bank-account.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('bank-account.create', $this->data);
    }

    /**
     * Show form to create a new bank account.
     *
     * Purpose: Prepare currencies and view for account creation.
     * Inputs: optional AJAX request
     * Outputs: AJAX fragment or full view for creating bank account
     * Side effects: Aborts with 403 if user lacks add permission
     */

    public function store(StoreAccount $request)
    {
        $this->addPermission = user()->permission('add_bankaccount');
        abort_403(!in_array($this->addPermission, ['all']));

        $account = new BankAccount();
        $account->type = $request->type;
        $account->account_name = $request->account_name;
        $account->account_type = $request->account_type;
        $account->currency_id = $request->currency_id;
        $account->contact_number = $request->contact_number;
        $account->opening_balance = round($request->opening_balance, 2);
        $account->status = $request->status;

        if ($request->type == 'bank') {
            $account->bank_name = $request->bank_name;
            $account->account_number = $request->account_number;

            if ($request->hasFile('bank_logo')) {
                $account->bank_logo = Files::uploadLocalOrS3($request->bank_logo, BankAccount::FILE_PATH);
            }

        }

        $account->save();

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('bankaccounts.index')]);
    }

    /**
     * Store a newly created bank account.
     *
     * Purpose: Validate input (via StoreAccount), create BankAccount record and optionally upload bank logo.
     * Inputs: StoreAccount validated request
     * Outputs: JSON success with redirect URL
     * Side effects: Writes BankAccount record and stores uploaded logo file
     */

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->bankaccount = BankAccount::findOrFail($id);
        $this->viewPermission = user()->permission('view_bankaccount');

        abort_403(!(
            $this->viewPermission == 'all' || ($this->viewPermission == 'added' && $this->bankaccount->added_by == user()->id)
        ));

        $this->pageTitle = $this->bankaccount->bank_name . ' ' . $this->bankaccount->account_name;
        $this->month = now(company()->timezone)->month;
        $this->year = now(company()->timezone)->year;
        $this->creditVsDebitChart = $this->creditVsDebitChart($id);
        $this->recentTransactions = BankTransaction::where('bank_account_id', $id)->orderByDesc('transaction_date')->orderByDesc('id')->limit(15)->get();

        $dataTable = new BankTransactionDataTable();

        $this->view = 'bank-account.bank-transaction';

        return $dataTable->render('bank-account.show', $this->data);
    }

    /**
     * Display a specific bank account and its recent transactions.
     *
     * Purpose: Load BankAccount and associated transaction statistics and render transaction DataTable.
     * Inputs: $id bank account id
     * Outputs: Rendered view for bank-account.show (transaction list) or AJAX response
     * Side effects: Aborts with 403 when user lacks view permission
     */

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->bankAccount = BankAccount::findOrFail($id);
        $this->editPermission = user()->permission('edit_bankaccount');

        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->bankAccount->added_by == user()->id)));

        $this->pageTitle = __('modules.bankaccount.updateBankAccount');

        $this->currencies = Currency::all();

        $this->view = 'bank-account.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('bank-account.create', $this->data);
    }

    /**
     * Show the form for editing a bank account.
     *
     * Purpose: Load BankAccount for edit, prepare currencies list and edit view.
     * Inputs: $id bank account id
     * Outputs: AJAX fragment or full view used for editing
     * Side effects: Aborts with 403 when user lacks edit permission
     */

    public function update(StoreAccount $request, $id)
    {
        $account = BankAccount::findOrFail($id);
        $this->editPermission = user()->permission('edit_bankaccount');

        abort_403(!(
            $this->editPermission == 'all' || ($this->editPermission == 'added' && $account->added_by == user()->id)
        ));

        $account->type = $request->type;
        $account->account_name = $request->account_name;
        $account->account_type = $request->account_type;
        $account->currency_id = $request->currency_id;
        $account->contact_number = $request->contact_number;
        $account->opening_balance = round($request->opening_balance, 2);
        $account->status = $request->status;

        if ($request->type == 'bank') {
            $account->bank_name = $request->bank_name;
            $account->account_number = $request->account_number;

            if ($request->bank_logo_delete == 'yes') {
                Files::deleteFile($account->bank_logo, BankAccount::FILE_PATH);
                $account->bank_logo = null;
            }

            if ($request->hasFile('bank_logo')) {
                Files::deleteFile($account->bank_logo, BankAccount::FILE_PATH);

                $account->bank_logo = Files::uploadLocalOrS3($request->bank_logo, BankAccount::FILE_PATH);
            }
        }

        $account->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('bankaccounts.index')]);
    }

    /**
     * Update a bank account record.
     *
     * Purpose: Apply validated changes to BankAccount and handle logo updates/deletion.
     * Inputs: StoreAccount validated request, $id bank account id
     * Outputs: JSON success with redirect URL
     * Side effects: Updates DB record and file storage
     */

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        $bankaccount = BankAccount::findOrFail($id);
        $this->deletePermission = user()->permission('delete_bankaccount');
        abort_403(!(
            $this->deletePermission == 'all' || ($this->deletePermission == 'added' && $bankaccount->added_by == user()->id)
        ));

        BankAccount::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('bankaccounts.index')]);

    }

    /**
     * Remove a bank account.
     *
     * Purpose: Delete BankAccount after permission checks and return redirect route.
     * Inputs: $id bank account id
     * Outputs: JSON success with redirect URL
     * Side effects: Deletes DB record
     */

    public function changeStatus(Request $request)
    {
        $accountId = $request->accountId;
        $status = $request->status;
        $bankAccount = BankAccount::findOrFail($accountId);

        $this->editPermission = user()->permission('edit_bankaccount');

        abort_403(!(
            $this->editPermission == 'all' || ($this->editPermission == 'added' && $bankAccount->added_by == user()->id)
        ));

        $bankAccount->status = $status;
        $bankAccount->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Toggle the active status of a bank account.
     *
     * Purpose: Update BankAccount.status to enable/disable account.
     * Inputs: Request with accountId and status
     * Outputs: JSON success
     * Side effects: Persists status change to DB
     */

    public function applyQuickAction()
    {
        switch (request()->action_type) {
        case 'delete':
            $this->deleteRecords(request());

            return Reply::success(__('messages.deleteSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    /**
     * Apply quick bulk actions (delete) to bank accounts.
     *
     * Purpose: Delegate to deleteRecords for bulk deletions
     * Inputs: request()->action_type and row_ids
     * Outputs: JSON success or error
     */

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_bankaccount') != 'all');

        BankAccount::whereIn('id', explode(',', $request->row_ids))->forceDelete();
    }

    /**
     * Permanently delete multiple bank accounts.
     *
     * Purpose: Force delete bank accounts specified by row_ids.
     * Inputs: $request->row_ids comma-separated ids
     * Outputs: none (permission enforced)
     * Side effects: Removes DB rows permanently
     */

    public function createTransaction()
    {
        $this->type = request('type');

        if ($this->type == 'account') {
            $this->addPermission = user()->permission('add_bank_transfer');
        }
        elseif ($this->type == 'deposit') {
            $this->addPermission = user()->permission('add_bank_deposit');
        }
        else {
            $this->addPermission = user()->permission('add_bank_withdraw');
        }

        abort_403(!in_array($this->addPermission, ['all']));

        $this->accountId = request('accountId');
        $this->type = request('type');

        $this->currentAccount = BankAccount::findOrFail($this->accountId);
        $this->bankAccounts = BankAccount::where('id', '!=', $this->accountId)->where('company_id', company()->id)
            ->where('status', 1)->get();


        $this->view = 'bank-account.ajax.create-transaction';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('bank-account.create', $this->data);
    }

    /**
     * Show form for creating a bank transaction (transfer/deposit/withdrawal).
     *
     * Purpose: Determine transaction type, permissions and prepare account lists for the transaction form.
     * Inputs: request parameters 'type' and 'accountId'
     * Outputs: AJAX fragment or full view for transaction creation
     * Side effects: Aborts with 403 if user lacks the transaction-specific permission
     */

    public function storeTransaction(StoreTransaction $request)
    {
        if ($request->type == 'account') {
            $this->addPermission = user()->permission('add_bank_transfer');

        }
        elseif ($request->type == 'deposit') {
            $this->addPermission = user()->permission('add_bank_deposit');
        }
        else {
            $this->addPermission = user()->permission('add_bank_withdraw');
        }

        abort_403(!in_array($this->addPermission, ['all']));

        if (!($request->type == 'deposit')) {

            $bankAccount = BankAccount::find($request->from_bank_account);
            $bankBalance = $bankAccount->bank_balance;
            $totalBalance = $bankBalance - $request->amount;

            $transaction = new BankTransaction();
            $transaction->bank_account_id = $request->from_bank_account;
            $transaction->type = 'Dr';
            $transaction->transaction_date = now();
            $transaction->amount = round($request->amount, 2);
            $transaction->memo = $request->memo;
            $transaction->bank_balance = round($totalBalance, 2);
            $transaction->transaction_relation = 'bank';
            $transaction->title = $request->type == 'account' ? 'bank-account-transfer' : 'bank-account-withdraw';
            $transaction->save();

            $id = $request->from_bank_account;
        }

        if (!($request->type == 'withdraw')) {

            $bankAccount = BankAccount::find($request->to_bank_account);
            $bankBalance = $bankAccount->bank_balance;
            $totalBalance = $bankBalance + $request->amount * ($request->exchange_rate ?? 1);

            $transaction = new BankTransaction();
            $transaction->bank_account_id = $request->to_bank_account;
            $transaction->type = 'Cr';
            $transaction->transaction_date = now();
            $transaction->amount = round($request->amount * ($request->exchange_rate ?? 1), 2);
            $transaction->memo = $request->memo;
            $transaction->bank_balance = round($totalBalance, 2);
            $transaction->transaction_relation = 'bank';
            $transaction->title = $request->type == 'account' ? 'bank-account-transfer' : 'bank-account-deposit';
            $transaction->save();

            $id = $request->type == 'deposit' ? $request->to_bank_account : $request->from_bank_account;

        }

        /* @phpstan-ignore-next-line */
        return Reply::successWithData(__('messages.bankTransactionSuccess'), ['redirectUrl' => route('bankaccounts.show', $id)]);
    }

    /**
     * Store a bank transaction (transfer, deposit or withdrawal).
     *
     * Purpose: Validate permission, create BankTransaction debit/credit entries and compute new balances.
     * Inputs: StoreTransaction validated request (type, amount, accounts, exchange_rate, memo)
     * Outputs: JSON success with redirect to bank account show
     * Side effects: Writes BankTransaction(s) and updates balances implicitly via stored bank_balance
     */

    public function viewTransaction($id)
    {
        $this->bankTransaction = BankTransaction::with('bankAccount', 'bankAccount.currency')->findOrFail($id);

        $this->viewPermission = user()->permission('view_bankaccount');
        abort_403(!(
            $this->viewPermission == 'all' || ($this->viewPermission == 'added' && $this->bankTransaction->added_by == user()->id)
        ));

        $this->type = $this->bankTransaction->transaction_relation;

        $this->pageTitle = __('modules.bankaccount.bankTransaction');
        $this->view = 'bank-account.ajax.view-transaction';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('bank-account.create', $this->data);
    }

    /**
     * View a specific bank transaction.
     *
     * Purpose: Load BankTransaction with its account and currency and return view fragment or full page.
     * Inputs: $id transaction id
     * Outputs: AJAX fragment or full view
     * Side effects: Aborts with 403 when user lacks view permission
     */

    public function destroyTransaction(Request $request)
    {
        $bankTransaction = BankTransaction::findOrFail($request->transactionId);
        $this->deletePermission = user()->permission('delete_bankaccount');
        abort_403(!(
            $this->deletePermission == 'all' || ($this->deletePermission == 'added' && $bankTransaction->added_by == user()->id)
        ));

        BankTransaction::destroy($request->transactionId);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('bankaccounts.show', $bankTransaction->bank_account_id)]);
    }

    /**
     * Delete a specific bank transaction.
     *
     * Purpose: Remove single BankTransaction after permission checks and redirect back to account.
     * Inputs: request->transactionId
     * Outputs: JSON success with redirect URL
     * Side effects: Deletes DB row
     */

    public function applyTransactionQuickAction()
    {
        switch (request()->action_type) {
        case 'delete':
            $this->deleteTransactionRecords(request());

            return Reply::success(__('messages.deleteSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    /**
     * Apply quick actions for transactions (bulk delete).
     */

    protected function deleteTransactionRecords($request)
    {
        abort_403(user()->permission('delete_bankaccount') != 'all');

        BankTransaction::whereIn('id', explode(',', $request->row_ids))->forceDelete();
    }

    /**
     * Permanently delete multiple bank transactions.
     *
     * Purpose: Force delete BankTransaction records by provided row_ids.
     * Inputs: $request->row_ids
     * Outputs: none (permission enforced)
     */

    public function generateStatement($id)
    {
        $this->generatePermission = user()->permission('view_bankaccount');
        abort_403(!in_array($this->generatePermission, ['all', 'added']));

        $this->accountId = $id;

        return view('bank-account.generate-statement', $this->data);
    }

    /**
     * Show page to generate bank statement for an account.
     *
     * Purpose: Render statement generation UI after permission checks.
     * Inputs: $id bank account id
     * Outputs: statement generation view
     */

    public function getBankStatement(Request $request)
    {
        $pdfOption = $this->domPdfObjectForDownload($request);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Return downloadable PDF bank statement for requested date range.
     *
     * Purpose: Create PDF via domPdfObjectForDownload and return it for download.
     * Inputs: Request with accountId, startDate, endDate
     * Outputs: PDF download response
     */

    public function domPdfObjectForDownload($request)
    {
        $startDate = companyToDateString($request->startDate);
        $endDate = companyToDateString($request->endDate);

        $this->statements = BankAccount::with(['transaction' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('bank_transactions.transaction_date', [$startDate, $endDate])
                ->orderBy('bank_transactions.transaction_date', 'desc')
                ->orderBy('bank_transactions.created_at', 'desc');
        }])->where('id', $request->accountId)->first();

        $this->sDate = $request->startDate;
        $this->eDate = $request->endDate;

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('bank-account.pdf.statement', $this->data);
        $filename = 'bank-statement';

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    /**
     * Prepare DOMPDF object and filename for bank statement download.
     *
     * Purpose: Aggregate transactions between the provided dates and render the statement view into a PDF wrapper.
     * Inputs: request with accountId, startDate, endDate
     * Outputs: array containing 'pdf' object and 'fileName'
     */

    public function creditVsDebitChart($bankAccountId)
    {

        $period = now()->subMonth(3)->monthsUntil(now());
        /* @phpstan-ignore-line */
        $startDate = $period->startDate->startOfMonth();
        /* @phpstan-ignore-line */
        $endDate = $period->endDate->endOfMonth();
        /* @phpstan-ignore-line */

        $months = [];

        foreach ($period as $periodData) {
            $months[$periodData->format('m-Y')] = [
                'y' => $periodData->translatedFormat('F'),
                'a' => 0,
                'b' => 0
            ];
        }

        $creditAmount = BankTransaction::whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('type', 'Cr')
            ->where('bank_account_id', $bankAccountId)
            ->select(DB::raw('sum(amount) as credit'),
                DB::raw("DATE_FORMAT(transaction_date, '%m-%Y') date"),
                DB::raw('YEAR(transaction_date) year, MONTH(transaction_date) month'))
            ->orderBy('transaction_date')
            ->groupby('year', 'month')
            ->get()->keyBy('date');

        $debitAmount = BankTransaction::whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('bank_account_id', $bankAccountId)
            ->where('type', 'Dr')
            ->select(DB::raw('sum(amount) as debit'),
                DB::raw("DATE_FORMAT(transaction_date, '%m-%Y') date"),
                DB::raw('YEAR(transaction_date) year, MONTH(transaction_date) month'))
            ->orderBy('transaction_date')
            ->groupby('year', 'month')
            ->get()->keyBy('date');

        foreach ($months as $key => $month) {
            $joinings = 0;
            $exit = 0;

            if (isset($creditAmount[$key])) {
                $joinings = $creditAmount[$key]->credit;
                /* @phpstan-ignore-line */
            }

            if (isset($debitAmount[$key])) {
                $exit = $debitAmount[$key]->debit;
                /* @phpstan-ignore-line */
            }

            $graphData[] = [
                'y' => $months[$key]['y'],
                'a' => $joinings,
                'b' => $exit
            ];

        }

        $graphData = collect($graphData);
        /* @phpstan-ignore-line */

        $data['labels'] = $graphData->pluck('y');
        $data['values'][] = $graphData->pluck('a');
        $data['values'][] = $graphData->pluck('b');
        $data['colors'] = ['#28a745', '#d30000'];
        $data['name'][] = __('modules.bankaccount.credit');
        $data['name'][] = __('modules.bankaccount.debit');

        return $data;

    }

    /**
     * Prepare data for a credit vs debit chart for a given bank account over recent months.
     *
     * Purpose: Aggregate monthly credit/debit totals for the last 3 months and format data for chart rendering.
     * Inputs: $bankAccountId
     * Outputs: array with labels, values, colors and series names for frontend chart
     */

}
