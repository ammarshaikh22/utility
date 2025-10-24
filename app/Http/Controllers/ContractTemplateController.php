<?php

namespace App\Http\Controllers;

use App\DataTables\ContractTemplatesDataTable;
use App\Helper\Reply;
use App\Http\Requests\StoreContractTemplate;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\ContractType;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;

class ContractTemplateController extends AccountBaseController
{
    /**
     * Constructor for the ContractTemplateController.
     * Initializes the parent controller and sets the page title.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.contractTemplate';
    }

    /**
     * Displays the contract templates index page.
     * Validates user permissions, retrieves contract types and counts, and renders the DataTable for contract templates.
     *
     * @param ContractTemplatesDataTable $dataTable The DataTable instance for rendering the contract templates list.
     * @return \Illuminate\Http\Response
     */
    public function index(ContractTemplatesDataTable $dataTable)
    {
        // Restrict access if the user has no permission to manage contract templates
        abort_403(user()->permission('manage_contract_template') == 'none');

        // Fetch contract types and contract count
        $this->contractTypes = ContractType::all();
        $this->contractCounts = Contract::count();

        // Render the contract templates index view with the DataTable
        return $dataTable->render('contract-template.index', $this->data);
    }

    /**
     * Displays the form to create a new contract template.
     * Retrieves necessary data (clients, contract types, currencies) and renders the create view.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->contractId = request('id');
        $this->contract = null;

        // Load contract template if specified
        if ($this->contractId != '') {
            $this->contract = ContractTemplate::findOrFail($this->contractId);
        }

        // Fetch data for the form
        $this->clients = User::allClients();
        $this->contractTypes = ContractType::all();
        $this->currencies = Currency::all();

        $this->pageTitle = __('app.menu.addContractTemplate');
        $this->view = 'contract-template.ajax.create';

        // Handle AJAX requests by rendering the create view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main create view
        return view('contract-template.create', $this->data);
    }

    /**
     * Stores a new contract template.
     * Validates the input using the StoreContractTemplate request and saves the template.
     *
     * @param StoreContractTemplate $request The validated request containing contract template data.
     * @return array JSON response with success message and redirect URL.
     */
    public function store(StoreContractTemplate $request)
    {
        // Create and save a new contract template
        $contract = new ContractTemplate();
        $contract->subject = $request->subject;
        $contract->amount = $request->amount;
        $contract->currency_id = $request->currency_id;
        $contract->contract_type_id = $request->contract_type;
        $contract->description = trim_editor($request->description);
        $contract->contract_detail = trim_editor($request->description);
        $contract->added_by = user()->id;
        $contract->save();

        // Return success response with redirect to contract templates index
        return Reply::redirect(route('contract-template.index'), __('messages.recordSaved'));
    }

    /**
     * Displays the details of a specific contract template.
     * Validates user permissions and renders the overview view for the specified template.
     *
     * @param int $id The ID of the contract template to display.
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Fetch the contract template
        $this->contract = ContractTemplate::findOrFail($id);
        $this->manageContractTemplatePermission = user()->permission('manage_contract_template');

        // Restrict access if the user lacks appropriate permissions to manage contract templates
        abort_403(!in_array($this->manageContractTemplatePermission, ['all', 'added']));

        $this->pageTitle = __('app.menu.contractTemplate');
        $this->view = 'contract-template.ajax.overview';

        // Handle AJAX requests by rendering the overview view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main overview view
        return view('contract-template.create', $this->data);
    }

    /**
     * Displays the form to edit an existing contract template.
     * Validates user permissions, retrieves the template and necessary data, and renders the edit view.
     *
     * @param int $id The ID of the contract template to edit.
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Fetch the contract template
        $this->contract = ContractTemplate::findOrFail($id);
        $this->manageContractTemplatePermission = user()->permission('manage_contract_template');

        // Restrict access if the user lacks appropriate permissions to manage contract templates
        abort_403(!in_array($this->manageContractTemplatePermission, ['all', 'added']));

        // Fetch data for the form
        $this->contractTypes = ContractType::all();
        $this->currencies = Currency::all();

        $this->pageTitle = __('app.update') . ' ' . __('app.menu.contractTemplate');
        $this->view = 'contract-template.ajax.edit';

        // Handle AJAX requests by rendering the edit view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main edit view
        return view('contract-template.create', $this->data);
    }

    /**
     * Updates an existing contract template.
     * Validates the input using the StoreContractTemplate request and updates the template.
     *
     * @param StoreContractTemplate $request The validated request containing updated contract template data.
     * @param int $id The ID of the contract template to update.
     * @return array JSON response with success message and redirect URL.
     */
    public function update(StoreContractTemplate $request, $id)
    {
        // Fetch and update the contract template
        $contract = ContractTemplate::findOrFail($id);
        $contract->subject = $request->subject;
        $contract->amount = $request->amount;
        $contract->currency_id = $request->currency_id;
        $contract->contract_type_id = $request->contract_type;
        $contract->description = trim_editor($request->description);
        $contract->contract_detail = trim_editor($request->description);
        $contract->save();

        // Return success response with redirect to contract templates index
        return Reply::redirect(route('contract-template.index'), __('messages.updateSuccess'));
    }

    /**
     * Applies bulk actions (currently only delete) to selected contract templates.
     * Delegates to the deleteRecords method for the delete action.
     *
     * @param Request $request The request containing the action type and selected template IDs.
     * @return array JSON response with success or error message.
     */
    public function applyQuickAction(Request $request)
    {
        if ($request->action_type == 'delete') {
            // Delete selected contract templates
            $this->deleteRecords($request);
            return Reply::success(__('messages.deleteSuccess'));
        }

        // Return error if no valid action is selected
        return Reply::error(__('messages.selectAction'));
    }

    /**
     * Deletes multiple contract template records in bulk.
     * Validates user permissions and removes the specified templates.
     *
     * @param Request $request The request containing the IDs of templates to delete.
     * @return bool True if deletion is successful.
     */
    protected function deleteRecords($request)
    {
        // Restrict access if the user does not have 'all' or 'added' permission to manage contract templates
        abort_403(user()->permission('manage_contract_template') != 'all' && user()->permission('manage_contract_template') != 'added');

        // Delete the specified contract templates
        ContractTemplate::whereIn('id', explode(',', $request->row_ids))->delete();

        return true;
    }

    /**
     * Deletes a single contract template.
     * Removes the specified template and returns a success response.
     *
     * @param int $id The ID of the contract template to delete.
     * @return array JSON response with success message.
     */
    public function destroy($id)
    {
        // Fetch and delete the contract template
        $contract = ContractTemplate::findOrFail($id);
        $contract->destroy($id);

        // Return success response
        return Reply::success(__('messages.deleteSuccess'));
    }
}