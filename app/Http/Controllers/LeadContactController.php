<?php

namespace App\Http\Controllers;

use App\DataTables\DealsDataTable;
use App\DataTables\LeadContactDataTable;
use App\DataTables\LeadNotesDataTable;
use App\Enums\Salutation;
use App\Helper\Reply;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Http\Requests\Lead\StoreRequest;
use App\Http\Requests\Lead\UpdateRequest;
use App\Imports\LeadImport;
use App\Jobs\ImportLeadJob;
use App\Models\Deal;
use App\Models\LeadAgent;
use App\Models\LeadCategory;
use App\Models\Lead;
use App\Models\LeadCustomForm;
use App\Models\LeadPipeline;
use App\Models\LeadProduct;
use App\Models\LeadSource;
use App\Models\PipelineStage;
use App\Models\LeadStatus;
use App\Models\Product;
use App\Models\User;
use App\Traits\ImportExcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LeadContactController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'modules.leadContact.leadContacts';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leads', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Display a listing of lead contacts using a DataTable.
     * Checks user permissions and retrieves categories, sources, and employees for filtering.
     *
     * @param \App\DataTables\LeadContactDataTable $dataTable
     * @return mixed
     */
    public function index(LeadContactDataTable $dataTable)
    {
        $this->destroySession();
        $this->viewLeadPermission = $viewPermission = user()->permission('view_lead');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->categories = LeadCategory::get();
            $this->sources = LeadSource::get();
            $this->employees = User::allEmployees(null, 'active');
        }

        return $dataTable->render('lead-contact.index', $this->data);
    }

    /**
     * Display details of a specific lead contact.
     * Handles different tabs (profile, deals, notes) based on request parameters.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $this->leadContact = Lead::findOrFail($id)->withCustomFields();
        $this->viewPermission = user()->permission('view_lead');
        abort_403(!in_array($this->viewPermission, ['all', 'added', 'owned', 'both']));

        $this->pageTitle = $this->leadContact->client_name_salutation;
        $this->categories = LeadCategory::all();
        $this->leadFormFields = LeadCustomForm::with('customField')->where('status', 'active')->where('custom_fields_id', '!=', 'null')->get();
        $this->leadId = $id;

        $getCustomFieldGroupsWithFields = $this->leadContact->getCustomFieldGroupsWithFields();
        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->editLeadPermission = user()->permission('edit_lead');
        $this->deleteLeadPermission = user()->permission('delete_lead');

        $tab = request('tab');

        switch ($tab) {
        case 'deal':
            return $this->deals();
        case 'notes':
            return $this->notes();
        default:
            $this->view = 'lead-contact.ajax.profile';
            break;
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'profile';
        return view('lead-contact.show', $this->data);
    }

    /**
     * Display notes for a specific lead contact using a DataTable.
     * Validates user permissions before rendering the notes view.
     *
     * @return mixed
     */
    public function notes()
    {
        $dataTable = new LeadNotesDataTable();
        $viewPermission = user()->permission('view_deals');
        abort_403(!in_array($viewPermission, ['all', 'added', 'both']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';
        $this->view = 'lead-contact.ajax.notes';

        return $dataTable->render('lead-contact.show', $this->data);
    }

    /**
     * Display deals associated with a specific lead contact using a DataTable.
     * Validates user permissions and retrieves pipeline stages for rendering.
     *
     * @return mixed
     */
    public function deals()
    {
        $viewPermission = user()->permission('view_deals');
        abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

        $tab = request('tab');
        $this->pipelines = LeadPipeline::all();
        $defaultPipeline = $this->pipelines->filter(function ($value, $key) {
            return $value->default == 1;
        })->first();

        $this->stages = PipelineStage::where('lead_pipeline_id', $defaultPipeline->id)->get();
        $this->activeTab = $tab ?: 'profile';
        $this->view = 'lead-contact.ajax.deal';
        $dataTable = new DealsDataTable();

        return $dataTable->render('lead-contact.show', $this->data);
    }

    /**
     * Show the form for creating a new lead contact.
     * Retrieves necessary data for form fields and checks user permissions.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->pageTitle = __('modules.leadContact.createTitle');
        $this->addPermission = user()->permission('add_lead');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->employees = User::allEmployees(null, true);
        $defaultStatus = LeadStatus::where('default', '1')->first();
        $this->columnId = request('column_id') ?: $defaultStatus->id;

        $this->leadAgents = LeadAgent::whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->with('user')->get();

        $this->leadAgentArray = $this->leadAgents->pluck('user_id')->toArray();

        if (in_array(user()->id, $this->leadAgentArray)) {
            $this->myAgentId = $this->leadAgents->filter(function ($value, $key) {
                return $value->user_id == user()->id;
            })->first()->id;
        }

        $leadContact = new Lead();
        $getCustomField = $leadContact->getCustomFieldGroupsWithFields();

        if ($getCustomField) {
            $this->fields = $getCustomField->fields;
        }

        $this->sources = LeadSource::all();
        $this->categories = LeadCategory::all();
        $this->countries = countries();
        $this->salutations = Salutation::cases();
        $this->leadPipelines = LeadPipeline::orderBy('default', 'DESC')->get();
        $this->leadStages = PipelineStage::all();
        $this->products = Product::all();

        $this->view = 'lead-contact.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('lead-contact.create', $this->data);
    }

    /**
     * Store a new lead contact in storage.
     * Validates user permissions, saves lead details, and optionally creates a deal.
     *
     * @param \App\Http\Requests\Lead\StoreRequest $request
     * @return \App\Helper\Reply
     */
    public function store(StoreRequest $request)
    {
        $this->addPermission = user()->permission('add_lead');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $existingUser = User::select('id')
            ->whereHas('roles', function ($q) {
                $q->where('name', 'client');
            })->where('company_id', company()->id)
            ->where('email', $request->client_email)
            ->whereNotNull('email')
            ->first();

        $leadContact = new Lead();
        $leadContact->company_id = company()->id;
        $leadContact->salutation = $request->salutation;
        $leadContact->client_name = $request->client_name;
        $leadContact->client_email = $request->client_email;
        $leadContact->note = trim_editor($request->note);
        $leadContact->source_id = $request->source_id;
        $leadContact->client_id = $existingUser?->id;
        $leadContact->lead_owner = $request->lead_owner;
        $leadContact->company_name = $request->company_name;
        $leadContact->website = $request->website;
        $leadContact->address = $request->address;
        $leadContact->cell = $request->cell;
        $leadContact->office = $request->office;
        $leadContact->city = $request->city;
        $leadContact->state = $request->state;
        $leadContact->country = $request->country;
        $leadContact->postal_code = $request->postal_code;
        $leadContact->mobile = $request->mobile;

        if ($request->has('create_deal') && $request->create_deal == 'on') {
            Session::put('create_deal_with_lead', true);
            Session::put('deal_name', $request->name);
        }

        $leadContact->save();

        if ($request->has('create_deal') && $request->create_deal == 'on') {
            $this->storeDeal($request, $leadContact);
        }

        if ($request->custom_fields_data) {
            $leadContact->updateCustomFieldData($request->custom_fields_data);
        }

        $this->logSearchEntry($leadContact->id, $leadContact->client_name, 'lead-contact.show', 'lead');

        if ($leadContact->client_email) {
            $this->logSearchEntry($leadContact->id, $leadContact->client_name, 'lead-contact.show', 'lead');
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($request->add_more == 'true') {
            $html = $this->create();
            return Reply::successWithData(__('messages.recordSaved'), ['html' => $html, 'add_more' => true]);
        }

        if ($redirectUrl == '') {
            $redirectUrl = route('lead-contact.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Show the form for editing a lead contact.
     * Validates user permissions and retrieves necessary data for form fields.
     *
     * @param int $id
     * @return \App\Helper\Reply|\Illuminate\View\View
     */
    public function edit($id)
    {
        $this->leadContact = Lead::with('leadSource', 'category')->findOrFail($id)->withCustomFields();
        $this->deal = Deal::where('lead_id', $id)->first();
        $this->editPermission = user()->permission('edit_lead');
        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->leadContact->added_by == user()->id)
            || ($this->editPermission == 'owned' && $this->leadContact->lead_owner == user()->id)
            || ($this->editPermission == 'both' && ($this->leadContact->added_by == user()->id || user()->id == $this->leadContact->lead_owner))
        ));

        $this->leadAgents = LeadAgent::with('user')->whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->get();

        $getCustomFieldGroupsWithFields = $this->leadContact->getCustomFieldGroupsWithFields();
        $this->employees = User::allEmployees();

        $activeEmployees = $this->employees->filter(function ($employee) {
            return $employee->status !== 'deactive';
        });

        $selectedEmployee = $this->employees->firstWhere('id', $this->leadContact->lead_owner);

        if ($selectedEmployee && $selectedEmployee->status === 'deactive') {
            $this->employees = $activeEmployees->push($selectedEmployee);
        } else {
            $this->employees = $activeEmployees;
        }

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->sources = LeadSource::all();
        $this->categories = LeadCategory::all();
        $this->countries = countries();
        $this->pageTitle = __('modules.leadContact.updateTitle');
        $this->salutations = Salutation::cases();

        if (request()->ajax()) {
            $html = view('lead-contact.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'lead-contact.ajax.edit';
        return view('lead-contact.create', $this->data);
    }

    /**
     * Update an existing lead contact in storage.
     * Validates user permissions, updates lead details, and optionally updates associated deal.
     *
     * @param \App\Http\Requests\Lead\UpdateRequest $request
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function update(UpdateRequest $request, $id)
    {
        $leadContact = Lead::findOrFail($id);
        $this->editPermission = user()->permission('edit_lead');
        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $leadContact->added_by == user()->id)
            || ($this->editPermission == 'owned' && $leadContact->lead_owner == user()->id)
            || ($this->editPermission == 'both' && ($leadContact->added_by == user()->id || user()->id == $leadContact->lead_owner))
        ));

        $leadContact->salutation = $request->salutation;
        $leadContact->client_name = $request->client_name;
        $leadContact->client_email = $request->client_email;
        $leadContact->note = trim_editor($request->note);
        $leadContact->source_id = $request->source_id;
        $leadContact->lead_owner = $request->lead_owner;
        $leadContact->category_id = $request->category_id;
        $leadContact->company_name = $request->company_name;
        $leadContact->website = $request->website;
        $leadContact->address = $request->address;
        $leadContact->cell = $request->cell;
        $leadContact->office = $request->office;
        $leadContact->city = $request->city;
        $leadContact->state = $request->state;
        $leadContact->country = $request->country;
        $leadContact->postal_code = $request->postal_code;
        $leadContact->mobile = $request->mobile;
        $leadContact->save();

        $clientCreated = $request->create_client == "on" ? '1' : '0';
        Deal::where('lead_id', $leadContact->id)->update(['create_client' => $clientCreated]);

        if ($request->custom_fields_data) {
            $leadContact->updateCustomFieldData($request->custom_fields_data);
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('lead-contact.index')]);
    }

    /**
     * Delete a lead contact from storage.
     * Validates user permissions before deletion.
     *
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function destroy($id)
    {
        $leadContact = Lead::findOrFail($id);
        $this->deletePermission = user()->permission('delete_lead');
        abort_403(!($this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $leadContact->added_by == user()->id)
            || ($this->deletePermission == 'owned' && $leadContact->lead_owner == user()->id)
            || ($this->deletePermission == 'both' && ($leadContact->added_by == user()->id || user()->id == $leadContact->lead_owner))
        ));

        Lead::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Perform bulk deletion of lead contacts.
     * Deletes multiple lead contacts based on provided IDs.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Helper\Reply
     */
    public function applyQuickAction(Request $request)
    {
        Lead::whereIn('id', explode(',', $request->row_ids))->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Show the form for importing lead contacts via Excel.
     * Validates user permissions before rendering the import view.
     *
     * @return \App\Helper\Reply|\Illuminate\View\View
     */
    public function importLead()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.menu.lead');
        $this->addPermission = user()->permission('add_lead');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        if (request()->ajax()) {
            $html = view('leads.ajax.import', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'leads.ajax.import';
        return view('leads.create', $this->data);
    }

    /**
     * Process the uploaded Excel file for importing leads.
     * Initiates the import process and renders the import progress view.
     *
     * @param \App\Http\Requests\Admin\Employee\ImportRequest $request
     * @return \App\Helper\Reply
     */
    public function importStore(ImportRequest $request)
    {
        $rvalue = $this->importFileProcess($request, LeadImport::class);

        if ($rvalue == 'abort') {
            return Reply::error(__('messages.abortAction'));
        }

        $view = view('leads.ajax.import_progress', $this->data)->render();
        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    /**
     * Process the lead import job.
     * Handles the background processing of the imported leads.
     *
     * @param \App\Http\Requests\Admin\Employee\ImportProcessRequest $request
     * @return \App\Helper\Reply
     */
    public function importProcess(ImportProcessRequest $request)
    {
        $batch = $this->importJobProcess($request, LeadImport::class, ImportLeadJob::class);
        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }

    /**
     * Clear session data related to lead imports and deal creation.
     *
     * @return void
     */
    public function destroySession()
    {
        if (session()->has('is_imported')) {
            session()->forget('is_imported');
        }

        if (session()->has('leads')) {
            session()->forget('leads');
        }

        if (session()->has('leads_count')) {
            session()->forget('leads_count');
        }

        if (session()->has('total_leads')) {
            session()->forget('total_leads');
        }

        if (session()->has('create_deal_with_lead')) {
            session()->forget('create_deal_with_lead');
        }

        if (session()->has('deal_name')) {
            session()->forget('deal_name');
        }

        if (session()->has('duplicate_leads')) {
            session()->forget('duplicate_leads');
        }
    }

    /**
     * Store a new deal associated with a lead contact.
     * Validates user permissions and creates a deal with the provided details.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Lead $leadContact
     * @return void
     */
    public function storeDeal($request, $leadContact)
    {
        $this->addPermission = user()->permission('add_deals');
        abort_403(!in_array($this->addPermission, ['all', 'added']));
        $agentId = null;

        if (!is_null($request->agent_id)) {
            $leadAgent = LeadAgent::where('user_id', $request->agent_id)->where('lead_category_id', $request->category_id)->first();
            $agentId = isset($leadAgent) ? $leadAgent->id : null;
        }

        $deal = new Deal();
        $deal->name = $request->name;
        $deal->lead_id = $leadContact->id;
        $deal->next_follow_up = 'yes';
        $deal->category_id = $request->category_id;
        $deal->deal_watcher = $request->deal_watcher;
        $deal->lead_pipeline_id = $request->pipeline;
        $deal->pipeline_stage_id = $request->stage_id;
        $deal->create_client = $request->create_client == "on" ? '1' : '0';
        $deal->agent_id = $agentId;
        $deal->close_date = companyToYmd($request->close_date);
        $deal->value = ($request->value) ?: 0;
        $deal->currency_id = $this->company->currency_id;
        $deal->save();

        if (!is_null($request->product_id)) {
            $products = $request->product_id;

            foreach ($products as $product) {
                $leadProduct = new LeadProduct();
                $leadProduct->deal_id = $deal->id;
                $leadProduct->product_id = $product;
                $leadProduct->save();
            }
        }
    }
}