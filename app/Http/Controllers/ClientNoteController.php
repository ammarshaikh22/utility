<?php

namespace App\Http\Controllers;

use App\DataTables\ClientNotesDataTable;
use App\Helper\Reply;
use App\Http\Requests\Admin\Client\StoreClientNote;
use App\Models\ClientNote;
use App\Models\ClientUserNote;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientNoteController extends AccountBaseController
{
    /**
     * Constructor for the ClientNoteController.
     * Initializes the parent controller, sets the page title, and applies middleware to allow request processing.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.notes';
        $this->middleware(function ($request, $next) {
            return $next($request);
        });
    }

    /**
     * Displays the client notes index page.
     * Validates user permissions to view client notes and renders the DataTable for notes.
     *
     * @param ClientNotesDataTable $dataTable The DataTable instance for rendering the client notes list.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(ClientNotesDataTable $dataTable)
    {
        // Restrict access if the user has no permission to view client notes
        abort_403(in_array(user()->permission('view_client_note'), ['none']));

        // Retrieve permission to add client notes
        $this->addClientNotePermission = user()->permission('add_client_note');

        // Render the client notes index view with the DataTable
        return $dataTable->render('clients.notes.index', $this->data);
    }

    /**
     * Displays the form to create a new client note.
     * Validates user permissions, retrieves employees based on user role, and renders the create view.
     *
     * @return \Illuminate\Contracts\View\View|array
     */
    public function create()
    {
        // Restrict access if the user lacks appropriate permissions to add client notes
        abort_403(!in_array(user()->permission('add_client_note'), ['all', 'added', 'both']));
        $this->pageTitle = __('app.addClientNote');
        $this->clientId = request('client');
        $projectMember = [];

        // If the user is a client, fetch employees from projects they are associated with
        if (in_array('client', user_roles())) {
            $this->employees = [];
            $clientProject = Project::where('client_id', user()->id)->pluck('id')->toArray();

            if (!empty($clientProject)) {
                $member = ProjectMember::with('user')->whereIn('project_id', $clientProject)->get();
                foreach ($member as $members) {
                    $projectMember[] = $members->user;
                }
                $this->employees = $projectMember;
            }
        } else {
            // Otherwise, fetch all employees
            $this->employees = User::allEmployees();
        }

        $this->view = 'clients.notes.create';

        // Handle AJAX requests by rendering the create view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main create view
        return view('clients.create', $this->data);
    }

    /**
     * Displays the details of a specific client note.
     * Validates user permissions, checks for password protection, and renders the show view.
     *
     * @param int $id The ID of the client note to display.
     * @return \Illuminate\Contracts\View\View|array
     */
    public function show($id)
    {
        $this->note = ClientNote::findOrFail($id);
        $callingFunction = debug_backtrace()[1]['function']; // Check which function called this show function

        // Restrict access to password-protected notes unless called from showVerified
        if ($this->note->ask_password == 1 && $callingFunction != 'showVerified') {
            abort(403, __('messages.permissionDenied'));
        }

        // Fetch note members
        $this->noteMembers = $this->note->members->pluck('user_id')->toArray();
        $this->employees = User::whereIn('id', $this->noteMembers)->get();

        $viewClientNotePermission = user()->permission('view_client_note');

        // Restrict access based on user permissions or note visibility
        abort_403(!($viewClientNotePermission == 'all'
            || ($viewClientNotePermission == 'added' && $this->note->added_by == user()->id)
            || ($viewClientNotePermission == 'owned' && in_array(user()->id, $this->noteMembers) && in_array('employee', user_roles()))
            || ($viewClientNotePermission == 'both' && ($this->note->added_by == user()->id || in_array(user()->id, $this->noteMembers)))
            || (in_array('client', user_roles()) && $this->note->is_client_show == 1)
            || ($this->note->type == 0 && $viewClientNotePermission != 'none')
        ));

        $this->pageTitle = __('app.client') . ' ' . __('app.note');
        $this->view = 'clients.notes.show';

        // Handle AJAX requests by rendering the show view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main show view
        return view('clients.create', $this->data);
    }

    /**
     * Stores a new client note.
     * Validates user permissions, saves the note, and associates members if the note is private.
     *
     * @param StoreClientNote $request The validated request containing client note data.
     * @return array JSON response with success message and redirect URL.
     */
    public function store(StoreClientNote $request)
    {
        // Restrict access if the user lacks appropriate permissions to add client notes
        abort_403(!in_array(user()->permission('add_client_note'), ['all', 'added', 'both']));

        $this->employees = User::allEmployees();

        // Create and save a new client note
        $note = new ClientNote();
        $note->title = $request->title;
        $note->client_id = $request->client_id;
        $note->details = $request->details;
        $note->type = $request->type;

        // Set client visibility based on user role
        if (in_array('client', user_roles())) {
            $note->is_client_show = 1;
        } else {
            $note->is_client_show = $request->is_client_show ? $request->is_client_show : '';
        }

        $note->ask_password = $request->ask_password ? $request->ask_password : '';
        $note->save();

        // If the note is private, associate selected users
        if ($request->type == 1) {
            $users = $request->user_id;
            if (!is_null($users)) {
                foreach ($users as $user) {
                    ClientUserNote::firstOrCreate([
                        'user_id' => $user,
                        'client_note_id' => $note->id
                    ]);
                }
            }
        }

        // Return success response with appropriate redirect URL
        return Reply::successWithData(__('messages.recordSaved'), [
            'redirectUrl' => in_array('client', user_roles()) ? route('client-notes.index') : route('clients.show', $note->client_id) . '?tab=notes'
        ]);
    }

    /**
     * Displays the form to edit an existing client note.
     * Validates user permissions, retrieves the note and employees, and renders the edit view.
     *
     * @param int $id The ID of the client note to edit.
     * @return \Illuminate\Contracts\View\View|array
     */
    public function edit($id)
    {
        $this->pageTitle = __('app.editClientNote');
        $this->note = ClientNote::findOrFail($id);
        $editClientNotePermission = user()->permission('view_client_note');

        // Restrict access based on user permissions
        abort_403(!($editClientNotePermission == 'all'
            || ($editClientNotePermission == 'added' && user()->id == $this->note->added_by)
            || ($editClientNotePermission == 'both' && user()->id == $this->note->added_by)));

        $projectMember = [];

        // If the user is a client, fetch employees from associated projects
        if (in_array('client', user_roles())) {
            $this->employees = [];
            $clientProject = Project::where('client_id', user()->id)->pluck('id')->toArray();

            if (!empty($clientProject)) {
                $member = ProjectMember::with('user')->whereIn('project_id', $clientProject)->get();
                foreach ($member as $members) {
                    $projectMember[] = $members->user;
                }
                $this->employees = $projectMember;
            }
        } else {
            // Otherwise, fetch all employees
            $this->employees = User::allEmployees();
        }

        // Fetch note members
        $this->noteMembers = $this->note->members->pluck('user_id')->toArray();
        $this->clientId = $this->note->client_id;

        $this->view = 'clients.notes.edit';

        // Handle AJAX requests by rendering the edit view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main edit view
        return view('clients.create', $this->data);
    }

    /**
     * Updates an existing client note.
     * Validates the input, updates the note, and manages associated members if the note is private.
     *
     * @param StoreClientNote $request The validated request containing updated client note data.
     * @param int $id The ID of the client note to update.
     * @return array JSON response with success message and redirect URL.
     */
    public function update(StoreClientNote $request, $id)
    {
        // Fetch and update the client note
        $note = ClientNote::findOrFail($id);
        $note->title = $request->title;
        $note->details = $request->details;
        $note->type = $request->type;

        // Set client visibility based on user role
        if (in_array('client', user_roles())) {
            $note->is_client_show = 1;
        } else {
            $note->is_client_show = $request->is_client_show ? $request->is_client_show : '';
        }

        $note->ask_password = $request->ask_password ?: '';
        $note->save();

        // If the note is private, update associated members
        if ($request->type == 1) {
            // Delete existing member associations
            ClientUserNote::where('client_note_id', $note->id)->delete();

            $users = $request->user_id;
            if (!is_null($users)) {
                foreach ($users as $user) {
                    ClientUserNote::firstOrCreate([
                        'user_id' => $user,
                        'client_note_id' => $note->id
                    ]);
                }
            }
        }

        // Return success response with appropriate redirect URL
        return Reply::successWithData(__('messages.updateSuccess'), [
            'redirectUrl' => in_array('client', user_roles()) ? route('client-notes.index') : route('clients.show', $note->client_id) . '?tab=notes'
        ]);
    }

    /**
     * Deletes a client note.
     * Validates user permissions and removes the specified note.
     *
     * @param int $id The ID of the client note to delete.
     * @return array JSON response with success message.
     */
    public function destroy($id)
    {
        $this->contact = ClientNote::findOrFail($id);
        $this->deletePermission = user()->permission('delete_client_note');

        // Restrict access based on user permissions
        abort_403(!($this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $this->contact->added_by == user()->id)
            || ($this->deletePermission == 'both' && $this->contact->added_by == user()->id)));

        // Delete the client note
        $this->contact->delete();

        // Return success response
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Applies bulk actions (currently only delete) to selected client notes.
     * Delegates to the deleteRecords method for the delete action.
     *
     * @param Request $request The request containing the action type and selected note IDs.
     * @return array JSON response with success or error message.
     */
    public function applyQuickAction(Request $request)
    {
        if ($request->action_type == 'delete') {
            // Delete selected client notes
            $this->deleteRecords($request);
            return Reply::success(__('messages.deleteSuccess'));
        }

        // Return error if no valid action is selected
        return Reply::error(__('messages.selectAction'));
    }

    /**
     * Deletes multiple client note records in bulk.
     * Validates user permissions and removes the specified notes.
     *
     * @param Request $request The request containing the IDs of notes to delete.
     * @return bool True if deletion is successful.
     */
    protected function deleteRecords($request)
    {
        // Restrict access if the user does not have 'all' permission to delete client notes
        abort_403(user()->permission('delete_client_note') !== 'all');

        // Delete the specified client notes
        ClientNote::whereIn('id', explode(',', $request->row_ids))->delete();

        return true;
    }

    /**
     * Displays the password verification form for a password-protected client note.
     * Retrieves the specified note and renders the verify password view.
     *
     * @param int $id The ID of the client note.
     * @return \Illuminate\Contracts\View\View
     */
    public function askForPassword($id)
    {
        $this->note = ClientNote::findOrFail($id);

        // Render the verify password view
        return view('clients.notes.verify-password', $this->data);
    }

    /**
     * Verifies the password for a password-protected client note.
     * Checks if the provided password matches the client's password.
     *
     * @param Request $request The request containing the password to verify.
     * @return array JSON response with success or error message.
     */
    public function checkPassword(Request $request)
    {
        $this->client = User::findOrFail($this->user->id);

        // Verify the provided password
        if (Hash::check($request->password, $this->client->password)) {
            return Reply::success(__('messages.passwordMatched'));
        }

        // Return error if the password is incorrect
        return Reply::error(__('messages.incorrectPassword'));
    }

    /**
     * Displays a password-verified client note.
     * Calls the show method to display the note after password verification.
     *
     * @param int $id The ID of the client note to display.
     * @return \Illuminate\Contracts\View\View|array
     */
    public function showVerified($id)
    {
        return $this->show($id);
    }
}