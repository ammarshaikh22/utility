<?php

namespace App\Http\Controllers;

use App\DataTables\AppreciationsDataTable;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Appreciation\StoreRequest;
use App\Http\Requests\Appreciation\UpdateRequest;
use App\Models\Award;
use App\Models\User;
use App\Models\Appreciation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppreciationController extends AccountBaseController
{
    /**
     * Constructor for the AppreciationController.
     * Initializes the parent controller and sets the page title for the appreciation module.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.appreciation';
    }

    /**
     * Displays a listing of appreciations using a DataTable.
     * Checks view permissions and retrieves awards and employees for display.
     * Renders the index view with the DataTable.
     *
     * @param AppreciationsDataTable $dataTable The DataTable instance for rendering appreciations.
     * @return \Illuminate\Http\Response
     */
    public function index(AppreciationsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_appreciation');
        $this->appreciations = Award::with('awardIcon')->get();
        $this->employees = User::allEmployees(null, true, 'all');

        // Restrict access if user lacks valid view permissions
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('appreciations.index', $this->data);
    }

    /**
     * Shows the form for creating a new appreciation.
     * Checks add and view permissions, retrieves employees and active award types, and sets the page title.
     * Handles both AJAX and standard requests, rendering the appropriate view.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_appreciation');
        $this->viewPermission = user()->permission('view_appreciation');
        // Restrict access if user lacks 'all' add permission
        abort_403($this->addPermission != 'all');

        $this->employees = User::allEmployees(null, true, 'all');
        $this->appreciationTypes = Award::with('awardIcon')->where('status', 'active')->get();
        $this->pageTitle = __('modules.appreciations.appreciation');
        $this->empID = request()->empid;
        $this->view = 'appreciations.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('appreciations.create', $this->data);
    }

    /**
     * Stores a new appreciation in the database.
     * Validates the request, checks add permission, and saves the appreciation with associated data (award, summary, date, recipient, and optional image).
     * Returns a success response with a redirect URL.
     *
     * @param StoreRequest $request The validated request containing appreciation data.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $this->addPermission = user()->permission('add_appreciation');
        // Restrict access if user lacks 'all' add permission
        abort_403($this->addPermission != 'all');

        $appreciation = new Appreciation();
        $appreciation->award_id = $request->award;
        $appreciation->summary = trim_editor($request->summery);
        $appreciation->award_date = Carbon::createFromFormat($this->company->date_format, $request->award_date);
        $appreciation->award_to = $request->given_to;
        $appreciation->added_by = user()->id;

        // Handle image upload if provided
        if ($request->hasFile('photo')) {
            Files::deleteFile($appreciation->image, 'appreciation');
            $appreciation->image = Files::uploadLocalOrS3($request->photo, 'appreciation');
        }

        $appreciation->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('appreciations.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Displays the details of a specific appreciation.
     * Checks view permissions and ensures the user has access based on their role (all, added, owned, or both).
     * Renders the show view for AJAX or standard requests.
     *
     * @param int $id The ID of the appreciation to display.
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->appreciation = Appreciation::with('award')->findOrFail($id);
        $this->viewPermission = user()->permission('view_appreciation');
        $this->pageTitle = __('app.menu.appreciation');

        // Restrict access based on view permission and user relationship to the appreciation
        abort_403(!(
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->appreciation->added_by == user()->id)
            || ($this->viewPermission == 'owned' && $this->appreciation->award_to == user()->id)
            || ($this->viewPermission == 'both' && ($this->appreciation->added_by == user()->id || $this->appreciation->award_to == user()->id))
        ));

        $this->view = 'appreciations.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('appreciations.create', $this->data);
    }

    /**
     * Shows the form for editing an existing appreciation.
     * Checks edit permissions, retrieves the appreciation, and ensures the user has access.
     * Filters employees to include only active ones, adding the current recipient if deactivated.
     * Renders the edit view for AJAX or standard requests.
     *
     * @param int $id The ID of the appreciation to edit.
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_appreciation');
        $this->appreciation = Appreciation::findOrFail($id);

        // Restrict access based on edit permission and user relationship to the appreciation
        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->appreciation->added_by == user()->id)
            || ($this->editPermission == 'owned' && $this->appreciation->award_to == user()->id)
            || ($this->editPermission == 'both' && ($this->appreciation->added_by == user()->id || $this->appreciation->award_to == user()->id))
        ));

        $this->pageTitle = __('app.menu.appreciation');
        $this->employees = User::allEmployees(null, false, 'all');

        // Filter active employees, but include the current recipient if deactivated
        $activeEmployees = $this->employees->filter(function ($employee) {
            return $employee->status !== 'deactive';
        });

        $selectedEmployee = $this->employees->firstWhere('id', $this->appreciation->award_to);

        if ($selectedEmployee && $selectedEmployee->status === 'deactive') {
            $this->employees = $activeEmployees->push($selectedEmployee);
        } else {
            $this->employees = $activeEmployees;
        }

        $this->appreciationTypes = Award::with('awardIcon')->where('status', 'active')->get();

        $this->view = 'appreciations.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('appreciations.create', $this->data);
    }

    /**
     * Updates an existing appreciation in the database.
     * Validates the request, updates the appreciation with new data, and handles image updates or deletion.
     * Returns a success response with a redirect URL.
     *
     * @param UpdateRequest $request The validated request containing updated appreciation data.
     * @param int $id The ID of the appreciation to update.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $id)
    {
        $appreciation = Appreciation::findOrFail($id);
        $appreciation->award_id = $request->award;
        $appreciation->summary = trim_editor($request->summery);
        $appreciation->award_date = Carbon::createFromFormat($this->company->date_format, $request->award_date);
        $appreciation->award_to = $request->given_to;

        // Delete image if requested
        if ($request->photo_delete == 'yes') {
            Files::deleteFile($appreciation->image, 'appreciation');
            $appreciation->image = null;
        }

        // Handle new image upload if provided
        if ($request->hasFile('photo')) {
            Files::deleteFile($appreciation->image, 'appreciation');
            $appreciation->image = Files::uploadLocalOrS3($request->photo, 'appreciation', 300);
        }

        $appreciation->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('appreciations.index');
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Deletes a specific appreciation from the database.
     * Checks delete permissions and ensures the user has access based on their role.
     * Returns a success response with a redirect URL.
     *
     * @param int $id The ID of the appreciation to delete.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $this->appreciation = Appreciation::findOrFail($id);
        $this->deletePermission = user()->permission('delete_appreciation');
        // Restrict access based on delete permission and user relationship to the appreciation
        abort_403(!(
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $this->appreciation->added_by == user()->id)
            || ($this->deletePermission == 'owned' && $this->appreciation->award_to == user()->id)
            || ($this->deletePermission == 'both' && ($this->appreciation->added_by == user()->id || $this->appreciation->award_to == user()->id))
        ));

        Appreciation::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('appreciations.index')]);
    }

    /**
     * Handles quick actions, such as bulk deletion, for appreciations.
     * Processes the requested action type and delegates to the appropriate method.
     *
     * @param Request $request The request containing the action type and data.
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);
            return Reply::success(__('messages.deleteSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    /**
     * Deletes multiple appreciation records based on provided IDs.
     * Checks delete permission and removes specified records from the database.
     *
     * @param Request $request The request containing the IDs of records to delete.
     */
    protected function deleteRecords($request)
    {
        $deletePermission = user()->permission('delete_appreciation');
        // Restrict access if user lacks 'all' delete permission
        abort_403($deletePermission != 'all');
        $item = explode(',', $request->row_ids);

        // Remove 'on' from the list if present (likely for checkbox selections)
        if (($key = array_search('on', $item)) !== false) {
            unset($item[$key]);
        }

        Appreciation::whereIn('id', $item)->delete();
    }
}