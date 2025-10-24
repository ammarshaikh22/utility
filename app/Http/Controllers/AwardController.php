<?php

namespace App\Http\Controllers;

use App\DataTables\AwardDataTable;
use App\Helper\Reply;
use App\Http\Requests\Appreciation\AppreciationType\StoreRequest;
use App\Http\Requests\Appreciation\AppreciationType\UpdateRequest;
use App\Models\Award;
use App\Models\AwardIcon;
use Illuminate\Http\Request;

class AwardController extends AccountBaseController
{
    /**
     * Constructor for the AwardController.
     * Initializes the parent controller and sets the page title for the award management view.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.award';
    }

    /**
     * Displays the awards index page.
     * Validates user permissions to view appreciation records and renders the DataTable for awards.
     *
     * @param AwardDataTable $dataTable The DataTable instance for rendering the awards list.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(AwardDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_appreciation');

        // Restrict access if the user lacks appropriate permissions to view appreciation records
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        // Render the awards index view with the DataTable
        return $dataTable->render('awards.index', $this->data);
    }

    /**
     * Displays the form to create a new award type.
     * Validates user permissions to manage awards and retrieves available award icons.
     * Renders the create view for both AJAX and non-AJAX requests.
     *
     * @return \Illuminate\Contracts\View\View|array
     */
    public function create()
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        // Restrict access if the user does not have 'all' permission to manage awards
        abort_403(!($this->manageAppreciationPermission == 'all'));

        // Fetch all available award icons
        $this->icons = AwardIcon::all();

        // Set the page title for adding an appreciation type
        $this->pageTitle = __('modules.appreciations.addAppreciationType');

        $this->view = 'awards.ajax.create';

        // Handle AJAX requests by rendering the create view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main create view
        return view('awards.create', $this->data);
    }

    /**
     * Displays a quick-create form for adding a new award type.
     * Validates user permissions and retrieves available award icons.
     * Renders the quick-create view.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function quickCreate()
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        // Restrict access if the user does not have 'all' permission to manage awards
        abort_403(!($this->manageAppreciationPermission == 'all'));

        // Fetch all available award icons
        $this->icons = AwardIcon::all();

        // Set the page title for adding an appreciation type
        $this->pageTitle = __('modules.appreciations.addAppreciationType');

        // Render the quick-create view for appreciation types
        return view('appreciations.ajax.create_appreciation_type', $this->data);
    }

    /**
     * Stores a new award type from the quick-create form.
     * Validates user permissions and input, saves the new award, and returns updated award options.
     *
     * @param StoreRequest $request The validated request containing award data.
     * @return array JSON response with success message and updated award options.
     */
    public function quickStore(StoreRequest $request)
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        // Restrict access if the user does not have 'all' permission to manage awards
        abort_403(!($this->manageAppreciationPermission == 'all'));

        // Create and save a new award
        $award = new Award();
        $award->title = $request->title;
        $award->award_icon_id = $request->icon;
        $award->color_code = $request->color_code;
        $award->summary = $request->summery;
        $award->save();

        // Fetch active awards with their icons
        $awards = Award::with('awardIcon')->where('status', 'active')->get();

        // Generate HTML options for the awards dropdown
        $options = $this->options($awards, $award);

        // Return success response with the updated options
        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }

    /**
     * Generates HTML options for an awards dropdown menu.
     * Creates a select option list with award titles and icons, highlighting the selected award if provided.
     *
     * @param mixed $items The collection of awards to display.
     * @param mixed|null $group The selected award to highlight (optional).
     * @return string HTML string of select options.
     */
    public static function options($items, $group = null): string
    {
        $options = '<option value="">--</option>';

        foreach ($items as $item) {
            $name = $item->title;
            $selected = (!is_null($group) && ($item->id == $group->id)) ? 'selected' : '';
            $icon = "<i class='bi bi-" . $item->awardIcon->icon . "' style='color:" . $item->color_code . "'></i>     ";

            // Add option with icon and name
            $options .= '<option ' . $selected . '  data-content="' . $icon . ' ' . $name . '" value="' . $item->id . '">
                                                ' . $name . '
                                            </option>';
        }

        return $options;
    }

    /**
     * Stores a new award type from the main create form.
     * Validates user permissions and input, saves the new award, and redirects to the awards index.
     *
     * @param StoreRequest $request The validated request containing award data.
     * @return array JSON response with success message and redirect URL.
     */
    public function store(StoreRequest $request)
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        // Restrict access if the user does not have 'all' permission to manage awards
        abort_403(!($this->manageAppreciationPermission == 'all'));

        // Create and save a new award
        $appreciation = new Award();
        $appreciation->title = $request->title;
        $appreciation->award_icon_id = $request->icon;
        $appreciation->color_code = $request->color_code;
        $appreciation->summary = $request->summery;
        $appreciation->save();

        // Return success response with redirect to awards index
        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('awards.index')]);
    }

    /**
     * Displays the details of a specific award type.
     * Validates user permissions to view appreciation records and renders the show view.
     *
     * @param int $id The ID of the award to display.
     * @return \Illuminate\Contracts\View\View|array
     */
    public function show($id)
    {
        // Fetch the award by ID
        $this->appreciationType = Award::findOrFail($id);

        $this->manageAppreciationPermission = user()->permission('view_appreciation');
        // Restrict access if the user has no permission to view appreciation records
        abort_403(!($this->manageAppreciationPermission != 'none'));

        // Set the page title to the award's title
        $this->pageTitle = $this->appreciationType->title;

        $this->view = 'awards.ajax.show';

        // Handle AJAX requests by rendering the show view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main show view
        return view('awards.create', $this->data);
    }

    /**
     * Displays the form to edit an existing award type.
     * Validates user permissions, retrieves the award and available icons, and renders the edit view.
     *
     * @param int $id The ID of the award to edit.
     * @return \Illuminate\Contracts\View\View|array
     */
    public function edit($id)
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        // Restrict access if the user does not have 'all' permission to manage awards
        abort_403(!($this->manageAppreciationPermission == 'all'));

        // Fetch the award by ID
        $this->appreciationType = Award::findOrFail($id);

        // Fetch all available award icons
        $this->icons = AwardIcon::all();
        $this->pageTitle = __('modules.awards.appreciationType');

        $this->view = 'awards.ajax.edit';

        // Handle AJAX requests by rendering the edit view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main edit view
        return view('awards.create', $this->data);
    }

    /**
     * Updates an existing award type.
     * Validates user permissions and input, updates the award, and redirects to the awards index.
     *
     * @param UpdateRequest $request The validated request containing updated award data.
     * @param int $id The ID of the award to update.
     * @return array JSON response with success message and redirect URL.
     */
    public function update(UpdateRequest $request, $id)
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        // Restrict access if the user does not have 'all' permission to manage awards
        abort_403(!($this->manageAppreciationPermission == 'all'));

        // Fetch and update the award
        $appreciation = Award::findOrFail($id);
        $appreciation->title = $request->title;
        $appreciation->award_icon_id = $request->icon;
        $appreciation->summary = $request->summery;
        $appreciation->color_code = $request->color_code;
        $appreciation->status = $request->status;
        $appreciation->save();

        // Return success response with redirect to awards index
        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('awards.index')]);
    }

    /**
     * Deletes an award type.
     * Validates user permissions and removes the specified award.
     *
     * @param int $id The ID of the award to delete.
     * @return array JSON response with success message and redirect URL.
     */
    public function destroy($id)
    {
        $this->manageAppreciationPermission = user()->permission('manage_award');
        // Restrict access if the user does not have 'all' permission to manage awards
        abort_403(!($this->manageAppreciationPermission == 'all'));

        // Delete the award
        Award::destroy($id);

        // Return success response with redirect to awards index
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('awards.index')]);
    }

    /**
     * Changes the status of an award type (e.g., active/inactive).
     * Validates user permissions and updates the award's status.
     *
     * @param Request $request The request containing the award ID and new status.
     * @return array JSON response with success message.
     */
    public function changeStatus(Request $request)
    {
        // Restrict access if the user does not have 'all' permission to manage awards
        abort_403(user()->permission('manage_award') != 'all');

        // Fetch and update the award's status
        $appreciationId = $request->appreciationId;
        $status = $request->status;
        $award = Award::findOrFail($appreciationId);
        $award->status = $status;
        $award->save();

        // Return success response
        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Applies bulk actions (delete or change status) to selected awards.
     * Handles the specified action type and delegates to appropriate methods.
     *
     * @param Request $request The request containing the action type and selected award IDs.
     * @return array JSON response with success or error message.
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
            case 'delete':
                // Delete selected awards
                $this->deleteRecords($request);
                return Reply::success(__('messages.deleteSuccess'));

            case 'change-leave-status':
                // Change status of selected awards
                $this->changeBulkStatus($request);
                return Reply::success(__('messages.updateSuccess'));

            default:
                // Return error if no valid action is selected
                return Reply::error(__('messages.selectAction'));
        }
    }

    /**
     * Deletes multiple award records in bulk.
     * Validates user permissions and removes the specified awards.
     *
     * @param Request $request The request containing the IDs of awards to delete.
     * @return void
     */
    protected function deleteRecords($request)
    {
        // Restrict access if the user does not have 'all' permission to manage awards
        abort_403(user()->permission('manage_award') != 'all');

        // Parse the list of award IDs, removing invalid entries
        $item = explode(',', $request->row_ids);
        if (($key = array_search('on', $item)) !== false) {
            unset($item[$key]);
        }

        // Delete the specified awards
        Award::whereIn('id', $item)->delete();
    }

    /**
     * Changes the status of multiple award records in bulk.
     * Validates user permissions and updates the status of the specified awards.
     *
     * @param Request $request The request containing the IDs and new status for awards.
     * @return void
     */
    protected function changeBulkStatus($request)
    {
        // Restrict access if the user does not have 'all' permission to manage awards
        abort_403(user()->permission('manage_award') != 'all');

        // Parse the list of award IDs, removing invalid entries
        $item = explode(',', $request->row_ids);
        if (($key = array_search('on', $item)) !== false) {
            unset($item[$key]);
        }

        // Update the status of the specified awards
        Award::whereIn('id', $item)->update(['status' => $request->status]);
    }
}