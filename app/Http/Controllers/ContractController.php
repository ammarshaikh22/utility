<?php

namespace App\Http\Controllers;

use App\DataTables\ContractsDataTable;
use App\Events\ContractSignedEvent;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Admin\Contract\StoreRequest;
use App\Http\Requests\Admin\Contract\UpdateRequest;
use App\Http\Requests\ClientContracts\SignRequest;
use App\Models\BaseModel;
use App\Models\Contract;
use App\Models\ContractSign;
use App\Models\ContractTemplate;
use App\Models\ContractType;
use App\Models\Currency;
use App\Models\Project;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Helper\UserService;
use App\Models\ClientContact;

class ContractController extends AccountBaseController
{
    /**
     * Constructor for the ContractController.
     * Initializes the parent controller, sets the page title, and applies middleware to restrict access to users with the contracts module enabled.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.contracts';
        $this->middleware(function ($request, $next) {
            // Restrict access if the contracts module is not enabled for the user
            abort_403(!in_array('contracts', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Displays the contracts index page.
     * Validates user permissions to view contracts, retrieves contract statistics, and renders the DataTable for contracts.
     *
     * @param ContractsDataTable $dataTable The DataTable instance for rendering the contracts list.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(ContractsDataTable $dataTable)
    {
        // Restrict access if the user has no permission to view contracts
        abort_403(user()->permission('view_contract') == 'none');

        if (!request()->ajax()) {
            // Fetch projects and clients based on user role
            $this->projects = Project::allProjects();
            if (in_array('client', user_roles())) {
                $this->clients = User::client();
            } else {
                $this->clients = User::allClients();
            }

            // Fetch contract data and statistics
            $this->contract = Contract::all();
            $this->contractTypes = ContractType::all();
            $this->contractCounts = Contract::count();
            $this->expiredCounts = Contract::where(DB::raw('DATE(`end_date`)'), '<', now()->format('Y-m-d'))->count();
            $this->aboutToExpireCounts = Contract::where(DB::raw('DATE(`end_date`)'), '>', now()->format('Y-m-d'))
                ->where(DB::raw('DATE(`end_date`)'), '<', now()->timezone($this->company->timezone)->addDays(7)->format('Y-m-d'))
                ->count();
        }

        // Render the contracts index view with the DataTable
        return $dataTable->render('contracts.index', $this->data);
    }

    /**
     * Applies bulk actions (currently only delete) to selected contracts.
     * Delegates to the deleteRecords method for the delete action.
     *
     * @param Request $request The request containing the action type and selected contract IDs.
     * @return array JSON response with success or error message.
     */
    public function applyQuickAction(Request $request)
    {
        if ($request->action_type == 'delete') {
            // Delete selected contracts
            $this->deleteRecords($request);
            return Reply::success(__('messages.deleteSuccess'));
        }

        // Return error if no valid action is selected
        return Reply::error(__('messages.selectAction'));
    }

    /**
     * Deletes multiple contract records in bulk.
     * Validates user permissions and removes the specified contracts.
     *
     * @param Request $request The request containing the IDs of contracts to delete.
     * @return bool True if deletion is successful.
     */
    protected function deleteRecords($request)
    {
        // Restrict access if the user does not have 'all' permission to delete contracts
        abort_403(user()->permission('delete_contract') !== 'all');

        // Delete the specified contracts
        Contract::whereIn('id', explode(',', $request->row_ids))->delete();

        return true;
    }

    /**
     * Deletes a single contract.
     * Validates user permissions and removes the specified contract.
     *
     * @param int $id The ID of the contract to delete.
     * @return array JSON response with success message.
     */
    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);
        $this->deletePermission = user()->permission('delete_contract');
        $userId = UserService::getUserId();

        // Restrict access based on user permissions
        abort_403(!(
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $userId == $contract->added_by)
            || ($this->deletePermission == 'owned' && $userId == $contract->client_id)
            || ($this->deletePermission == 'both' && ($userId == $contract->client_id || $userId == $contract->added_by))
        ));

        // Delete the contract
        Contract::destroy($id);

        // Return success response
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Displays the form to create a new contract.
     * Validates user permissions, retrieves necessary data (clients, contract types, currencies, projects, templates), and renders the create view.
     *
     * @return \Illuminate\Contracts\View\View|array
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_contract');
        // Restrict access if the user lacks appropriate permissions to add contracts
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->contractId = request('id');
        $this->contract = null;

        // Load contract template if specified
        if ($this->contractId != '') {
            $this->contractTemplate = Contract::findOrFail($this->contractId);
        }

        // Fetch data for the form
        $this->templates = ContractTemplate::all();
        $this->clients = User::allClients(null, overRidePermission: ($this->addPermission == 'all' ? 'all' : null));
        $this->contractTypes = ContractType::all();
        $this->currencies = Currency::all();
        $this->projects = Project::all();

        // Generate contract number
        $this->lastContract = Contract::lastContractNumber() + 1;
        $this->invoiceSetting = invoice_setting();
        $this->zero = '';

        if (strlen($this->lastContract) < $this->invoiceSetting->contract_digit) {
            $condition = $this->invoiceSetting->contract_digit - strlen($this->lastContract);
            for ($i = 0; $i < $condition; $i++) {
                $this->zero = '0' . $this->zero;
            }
        }

        // Load contract template if specified in request
        if (is_null($this->contractId)) {
            $this->contractTemplate = request('template') ? ContractTemplate::findOrFail(request('template')) : null;
        }

        $contract = new Contract();
        $getCustomFieldGroupsWithFields = $contract->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->pageTitle = __('app.menu.addContract');
        $this->view = 'contracts.ajax.create';

        // Handle AJAX requests by rendering the create view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main create view
        return view('contracts.create', $this->data);
    }

    /**
     * Stores a new contract.
     * Validates the input using the StoreRequest and saves the contract using the storeUpdate method.
     *
     * @param StoreRequest $request The validated request containing contract data.
     * @return array JSON response with success message and redirect URL.
     */
    public function store(StoreRequest $request)
    {
        $contract = new Contract();
        $this->storeUpdate($request, $contract);

        // Return success response with redirect to contracts index
        return Reply::redirect(route('contracts.index'), __('messages.recordSaved'));
    }

    /**
     * Displays the form to edit an existing contract.
     * Validates user permissions, retrieves the contract and necessary data, and renders the edit view.
     *
     * @param int $id The ID of the contract to edit.
     * @return \Illuminate\Contracts\View\View|array
     */
    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_contract');
        $this->contract = Contract::with('signature', 'renewHistory', 'renewHistory.renewedBy')
            ->findOrFail($id)
            ->withCustomFields();

        $this->projects = Project::all();
        $userId = UserService::getUserId();

        // Restrict access based on user permissions
        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && $userId == $this->contract->added_by)
            || ($this->editPermission == 'owned' && $userId == $this->contract->client_id)
            || ($this->editPermission == 'both' && ($userId == $this->contract->client_id || $userId == $this->contract->added_by))
        ));

        // Fetch data for the form
        $this->clients = User::allClients(null, overRidePermission: ($this->editPermission == 'all' ? 'all' : null));
        $this->contractTypes = ContractType::all();
        $this->currencies = Currency::all();
        $this->pageTitle = $this->contract->contract_number;

        $contract = new Contract();
        $getCustomFieldGroupsWithFields = $contract->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'contracts.ajax.edit';

        // Handle AJAX requests by rendering the edit view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main edit view
        return view('contracts.create', $this->data);
    }

    /**
     * Updates an existing contract.
     * Validates the input using the UpdateRequest and updates the contract using the storeUpdate method.
     *
     * @param UpdateRequest $request The validated request containing updated contract data.
     * @param int $id The ID of the contract to update.
     * @return array JSON response with success message and redirect URL.
     */
    public function update(UpdateRequest $request, $id)
    {
        $contract = Contract::findOrFail($id);
        $this->storeUpdate($request, $contract);

        // Return success response with redirect to contracts index
        return Reply::redirect(route('contracts.index'), __('messages.updateSuccess'));
    }

    /**
     * Handles the storage or update of a contract.
     * Populates the contract model with request data and saves it, including custom fields if provided.
     *
     * @param Request $request The request containing contract data.
     * @param Contract $contract The contract model to store or update.
     * @return Contract The saved contract model.
     */
    private function storeUpdate($request, $contract)
    {
        $contract->client_id = $request->client_id;
        $contract->project_id = $request->project_id;
        $contract->subject = $request->subject;
        $contract->amount = $request->amount;
        $contract->currency_id = $request->currency_id;
        $contract->original_amount = $request->amount;
        $contract->contract_name = $request->contract_name;
        $contract->alternate_address = $request->alternate_address;
        $contract->contract_note = $request->note;
        $contract->cell = $request->cell;
        $contract->office = $request->office;
        $contract->city = $request->city;
        $contract->state = $request->state;
        $contract->country = $request->country;
        $contract->postal_code = $request->postal_code;
        $contract->contract_type_id = $request->contract_type;
        $contract->contract_number = $request->contract_number;
        $contract->start_date = companyToYmd($request->start_date);
        $contract->original_start_date = companyToYmd($request->start_date);
        $contract->end_date = $request->end_date == null ? $request->end_date : companyToYmd($request->end_date);
        $contract->original_end_date = $request->end_date == null ? $request->end_date : companyToYmd($request->end_date);
        $contract->description = trim_editor($request->description);
        $contract->contract_detail = trim_editor($request->description);
        $contract->save();

        // Update custom fields if provided
        if ($request->custom_fields_data) {
            $contract->updateCustomFieldData($request->custom_fields_data);
        }

        return $contract;
    }

    /**
     * Displays the details of a specific contract.
     * Validates user permissions, retrieves the contract and related data, and renders the appropriate tab view.
     *
     * @param int $id The ID of the contract to display.
     * @return \Illuminate\Contracts\View\View|array
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_contract');
        $this->addContractPermission = user()->permission('add_contract');
        $this->editContractPermission = user()->permission('edit_contract');
        $this->deleteContractPermission = user()->permission('delete_contract');
        $this->viewDiscussionPermission = user()->permission('view_contract_discussion');
        $this->viewContractFilesPermission = user()->permission('view_contract_files');
        $this->userId = UserService::getUserId();

        $this->cId = $this->id = [];

        // If the user is a client contact, fetch associated client IDs
        if (in_array('client', user_roles()) && user()->is_client_contact == null) {
            $this->cId = $this->id = ClientContact::where('user_id', user()->id)->pluck('client_id')->toArray();
        }

        // Fetch the contract with related data
        $this->contract = Contract::with(['signature', 'client', 'client.clientDetails', 'files' => function ($q) use ($this->viewContractFilesPermission) {
            if ($this->viewContractFilesPermission == 'added') {
                $q->where('added_by', $this->userId);
            }
        }, 'renewHistory', 'renewHistory.renewedBy',
            'discussion' => function ($q) use ($this->viewDiscussionPermission) {
                if ($this->viewDiscussionPermission == 'added') {
                    $q->where('contract_discussions.added_by', $this->userId);
                }
            }, 'discussion.user'])->findOrFail($id)->withCustomFields();

        // Restrict access based on user permissions
        abort_403(!(
            $viewPermission == 'all'
            || ($viewPermission == 'added' && $this->userId == $this->contract->added_by)
            || ($viewPermission == 'owned' && $this->userId == $this->contract->client_id)
            || ($viewPermission == 'both' && ($this->userId == $this->contract->client_id || $this->userId == $this->contract->added_by))
        ));

        $contract = new Contract();
        $getCustomFieldGroupsWithFields = $contract->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->pageTitle = $this->contract->contract_number;
        $tab = request('tab');

        // Determine the view based on the requested tab
        $this->view = match ($tab) {
            'discussion' => 'contracts.ajax.discussion',
            'files' => 'contracts.ajax.files',
            'renew' => 'contracts.ajax.renew',
            default => 'contracts.ajax.summary',
        };

        // Handle AJAX requests by rendering the appropriate view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'profile';

        // Render the main show view
        return view('contracts.show', $this->data);
    }

    /**
     * Downloads a contract as a PDF.
     * Validates user permissions, generates a PDF of the contract, and initiates the download.
     *
     * @param int $id The ID of the contract to download.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id)
    {
        $this->contract = Contract::findOrFail($id);
        $viewPermission = user()->permission('view_contract');
        $this->contract = Contract::with('signature', 'client', 'client.clientDetails', 'files')->findOrFail($id)->withCustomFields();
        $userId = UserService::getUserId();

        $getCustomFieldGroupsWithFields = $this->contract->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        // Restrict access based on user permissions
        abort_403(!(
            $viewPermission == 'all'
            || ($viewPermission == 'added' && $userId == $this->contract->added_by)
            || ($viewPermission == 'owned' && $userId == $this->contract->client_id)
            || ($viewPermission == 'both' && ($userId == $this->contract->client_id || $userId == $this->contract->added_by))
        ));

        // Generate PDF
        $pdf = app('dompdf.wrapper');
        $this->company = $this->settings = company();
        $this->invoiceSetting = invoice_setting();

        $pdf->setOption('enable_php', true);
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');

        $customCss = '<style>
        * { text-transform: none !important; }
        </style>';

        $pdf->loadHTML($customCss . view('contracts.contract-pdf', $this->data)->render());
        $filename = 'contract-' . $this->contract->id;

        // Download the generated PDF
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Prepares a PDF view for a contract.
     * Retrieves the contract and generates a PDF object for viewing.
     *
     * @param int $id The ID of the contract.
     * @return array Array containing the PDF object and filename.
     */
    public function downloadView($id)
    {
        $this->contract = Contract::findOrFail($id)->withCustomFields();
        $pdf = app('dompdf.wrapper');

        $this->company = $this->settings = Company::findOrFail($this->contract->company_id);
        $this->invoiceSetting = invoice_setting();

        $getCustomFieldGroupsWithFields = $this->contract->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');
        $pdf->loadView('contracts.contract-pdf', $this->data);

        $filename = 'contract-' . $this->contract->id;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    /**
     * Signs a contract.
     * Validates the request, saves the signature details, and triggers a ContractSignedEvent.
     *
     * @param SignRequest $request The validated request containing signature data.
     * @param int $id The ID of the contract to sign.
     * @return array JSON response with redirect URL.
     */
    public function sign(SignRequest $request, $id)
    {
        $this->contract = Contract::with('signature')->findOrFail($id);

        // Check if the contract is already signed
        if ($this->contract && $this->contract->signature) {
            return Reply::error(__('messages.alreadySigned'));
        }

        // Save signature details
        $sign = new ContractSign();
        $sign->full_name = $request->first_name . ' ' . $request->last_name;
        $sign->contract_id = $this->contract->id;
        $sign->email = $request->email;
        $sign->date = now();
        $sign->place = $request->place;
        $imageName = null;

        // Handle signature image
        if ($request->signature_type == 'signature') {
            $image = $request->signature; // Base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';
            Files::createDirectoryIfNotExist('contract/sign');

            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/contract/sign/' . $imageName, base64_decode($image));
            Files::uploadLocalFile($imageName, 'contract/sign', $this->contract->company_id);
        } else {
            if ($request->hasFile('image')) {
                $imageName = Files::uploadLocalOrS3($request->image, 'contract/sign', 300);
            }
        }

        $sign->signature = $imageName;
        $sign->save();

        // Trigger contract signed event
        event(new ContractSignedEvent($this->contract, $sign));

        // Return success response with redirect to contract details
        return Reply::redirect(route('contracts.show', $this->contract->id));
    }

    /**
     * Adds a company signature to a contract.
     * Validates the request, saves the signature image, and updates the contract with signature details.
     *
     * @param Request $request The request containing signature data.
     * @return array JSON response with success message.
     */
    public function companySign(Request $request)
    {
        $contract = Contract::find($request->id);
        $imageName = null;
        $userId = UserService::getUserId();

        // Handle signature image
        if ($request->signature_type == 'signature') {
            $image = $request->signature; // Base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';
            Files::createDirectoryIfNotExist('contract/sign');

            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/contract/sign/' . $imageName, base64_decode($image));
            Files::uploadLocalFile($imageName, 'contract/sign', $contract->company_id);
        } else {
            if ($request->hasFile('image')) {
                $imageName = Files::uploadLocalOrS3($request->image, 'contract/sign', 300);
            }
        }

        // Update contract with company signature details
        $contract->company_sign = $imageName;
        $contract->sign_date = now();
        $contract->sign_by = $userId;
        $contract->update();

        // Return success response
        return Reply::successWithData(__('messages.signatureAdded'), ['status' => 'success']);
    }

    /**
     * Displays the form for adding a company signature to a contract.
     * Retrieves the contract and renders the company sign view.
     *
     * @param Request $request The request containing the contract ID.
     * @param int $id The ID of the contract.
     * @return \Illuminate\Contracts\View\View
     */
    public function companiesSign(Request $request, $id)
    {
        $this->contract = Contract::find($id);

        // Render the company sign view
        return view('contracts.companysign.sign', $this->data);
    }

    /**
     * Retrieves project and client details for a given client ID.
     * Fetches projects and client information, returning them as options for a dropdown.
     *
     * @param int $id The ID of the client.
     * @return array JSON response with project options and client details.
     */
    public function projectDetail($id)
    {
        $this->clientDetails = null;

        if ($id != 0) {
            $projects = Project::where('client_id', $id)->get();
            $this->clientDetails = User::where('id', $id)->first();

            $clientInfo = [
                'mobile' => $this->clientDetails->country_phonecode . ' ' . $this->clientDetails->mobile,
                'office_mobile' => $this->clientDetails->clientDetails->office,
                'city' => $this->clientDetails->clientDetails->city,
                'state' => $this->clientDetails->clientDetails->state,
                'countryName' => $this->clientDetails?->country?->name,
                'postalCode' => $this->clientDetails->clientDetails->postal_code,
            ];
        } else {
            $projects = Project::all();
        }

        $options = BaseModel::options($projects, null, 'project_name');

        // Return data-only response with project options and client details
        return Reply::dataOnly(['status' => 'success', 'data' => $options, 'clientDetails' => $clientInfo]);
    }

    /**
     * Displays the form for adding a company signature to a contract (alias for companiesSign).
     * Retrieves the contract and renders the company sign view.
     *
     * @param int $id The ID of the contract.
     * @return \Illuminate\Contracts\View\View
     */
    public function companySig($id)
    {
        $this->contract = Contract::find($id);

        // Render the company sign view
        return view('contracts.companysign.sign', $this->data);
    }
}