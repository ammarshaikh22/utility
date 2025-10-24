<?php

namespace App\Http\Controllers;

use App\DataTables\LeadNotesDataTable;
use App\Helper\Reply;
use App\Http\Requests\Lead\StoreLeadNote;
use App\Http\Requests\StoreDealNote;
use App\Models\DealNote;
use App\Models\User;
use Illuminate\Http\Request;

class DealNoteController extends AccountBaseController
{
    /**
     * Constructor for the DealNoteController.
     * Initializes the parent controller, sets the page title, and applies middleware to allow request processing.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.notes';
        $this->middleware(function ($request, $next) {
            // Allow the request to proceed without additional checks
            return $next($request);
        });
    }

    /**
     * Displays the deal notes index page.
     * Renders a datatable of deal notes, restricted to users with appropriate view permissions.
     *
     * @param LeadNotesDataTable $dataTable The datatable instance for rendering notes.
     * @return mixed The rendered datatable view.
     */
    public function index(LeadNotesDataTable $dataTable)
    {
        // Restrict access to users with 'all' or 'added' permission to view deal notes
        abort_403(!(in_array(user()->permission('view_deal_note'), ['all', 'added'])));

        // Render the deal notes index view with datatable
        return $dataTable->render('leads.notes.index', $this->data);
    }

    /**
     * Displays the form for creating a new deal note.
     * Retrieves employees and sets up the create view, restricted to users with appropriate add permissions.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        // Restrict access to users with 'all', 'added', or 'both' permission to add deal notes
        abort_403(!in_array(user()->permission('add_deal_note'), ['all', 'added', 'both']));

        // Fetch all employees and set lead ID from request
        $this->employees = User::allEmployees();
        $this->pageTitle = __('modules.deal.addDealNote');
        $this->leadId = request('lead');
        $this->view = 'leads.notes.create';

        // Handle AJAX requests by rendering the create view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the create deal note view
        return view('leads.create', $this->data);
    }

    /**
     * Displays a specific deal note.
     * Retrieves the note and its members, checks view permissions, and renders the show view.
     *
     * @param int $id The ID of the deal note to display.
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View JSON response for AJAX or rendered view.
     */
    public function show($id)
    {
        // Fetch the deal note and its members
        $this->note = DealNote::findOrFail($id);
        $this->noteMembers = $this->note->members->pluck('user_id')->toArray();
        $this->employees = User::whereIn('id', $this->noteMembers)->get();

        // Check view permissions
        $viewClientNotePermission = user()->permission('view_deal_note');
        $memberIds = $this->note->members->pluck('user_id')->toArray();
        abort_403(!($viewClientNotePermission == 'all'
            || ($viewClientNotePermission == 'added' && $this->note->added_by == user()->id)
            || ($viewClientNotePermission == 'owned' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
            || ($viewClientNotePermission == 'both' && (in_array(user()->id, $memberIds) || $this->note->added_by == user()->id))
        ));

        $this->pageTitle = __('modules.deal.dealNote');

        // Handle AJAX requests by rendering the show view
        if (request()->ajax()) {
            $html = view('leads.notes.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'leads.notes.show';
        return view('leads.create', $this->data);
    }

    /**
     * Stores a new deal note.
     * Validates the input using the StoreDealNote request, creates a new deal note, and redirects to the deal's notes tab.
     *
     * @param StoreDealNote $request The validated request containing deal note data.
     * @return array JSON response with success message and redirect URL.
     */
    public function store(StoreDealNote $request)
    {
        // Restrict access to users with 'all', 'added', or 'both' permission to add deal notes
        abort_403(!in_array(user()->permission('add_deal_note'), ['all', 'added', 'both']));

        // Create and save a new deal note
        $note = new DealNote();
        $note->title = $request->title;
        $note->deal_id = $request->lead_id;
        $note->details = trim_editor($request->details);
        $note->save();

        // Return success response with redirect to the deal's notes tab
        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('deals.show', $note->deal_id) . '?tab=notes']);
    }

    /**
     * Displays the form for editing an existing deal note.
     * Retrieves the note, checks edit permissions, and renders the edit view.
     *
     * @param int $id The ID of the deal note to edit.
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View JSON response for AJAX or rendered view.
     */
    public function edit($id)
    {
        $this->pageTitle = __('modules.deal.editDealNote');
        $this->note = DealNote::findOrFail($id);

        // Check edit permissions
        $editClientNotePermission = user()->permission('view_deal_note');
        $memberIds = $this->note->members->pluck('user_id')->toArray();
        abort_403(!($editClientNotePermission == 'all'
            || ($editClientNotePermission == 'added' && user()->id == $this->note->added_by)
            || ($editClientNotePermission == 'owned' && in_array('employee', user_roles()))
            || ($editClientNotePermission == 'both' && ($this->note->added_by == user()->id))
        ));

        $this->leadId = $this->note->deal_id;

        // Handle AJAX requests by rendering the edit view
        if (request()->ajax()) {
            $html = view('leads.notes.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'leads.notes.edit';
        return view('leads.create', $this->data);
    }

    /**
     * Updates an existing deal note.
     * Validates the input using the StoreDealNote request, updates the note, and redirects to the deal's notes tab.
     *
     * @param StoreDealNote $request The validated request containing updated deal note data.
     * @param int $id The ID of the deal note to update.
     * @return array JSON response with success message and redirect URL.
     */
    public function update(StoreDealNote $request, $id)
    {
        // Fetch and update the deal note
        $note = DealNote::findOrFail($id);
        $note->title = $request->title;
        $note->details = trim_editor($request->details);
        $note->save();

        // Return success response with redirect to the deal's notes tab
        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('deals.show', $note->deal_id) . '?tab=notes']);
    }

    /**
     * Deletes a deal note.
     * Checks delete permissions, removes the note, and redirects to the deal's notes tab.
     *
     * @param int $id The ID of the deal note to delete.
     * @return array JSON response with success message and redirect URL.
     */
    public function destroy($id)
    {
        $this->note = DealNote::findOrFail($id);
        $this->deletePermission = user()->permission('delete_deal_note');

        // Check delete permissions
        abort_403(!($this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $this->note->added_by == user()->id)
            || ($this->deletePermission == 'owned' && in_array('employee', user_roles()))
            || ($this->deletePermission == 'both' && ($this->note->added_by == user()->id))
        ));

        // Delete the deal note
        $this->note->delete();

        // Return success response with redirect to the deal's notes tab
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('deals.show', $this->note->deal_id) . '?tab=notes']);
    }

    /**
     * Applies a quick action (e.g., delete) to multiple deal notes.
     * Currently supports only the delete action for selected notes.
     *
     * @param Request $request The request containing the action type and row IDs.
     * @return array JSON response with success or error message.
     */
    public function applyQuickAction(Request $request)
    {
        if ($request->action_type == 'delete') {
            $this->deleteRecords($request);
            return Reply::success(__('messages.deleteSuccess'));
        }

        // Return error if an invalid action is selected
        return Reply::error(__('messages.selectAction'));
    }

    /**
     * Deletes multiple deal notes based on provided IDs.
     * Restricted to users with 'all' permission to delete deal notes.
     *
     * @param Request $request The request containing the row IDs to delete.
     * @return bool True if deletion is successful.
     */
    protected function deleteRecords($request)
    {
        // Restrict access to users with 'all' permission to delete deal notes
        abort_403(!(user()->permission('delete_deal_note') == 'all'));

        // Delete the specified deal notes
        DealNote::whereIn('id', explode(',', $request->row_ids))->delete();
        return true;
    }
}