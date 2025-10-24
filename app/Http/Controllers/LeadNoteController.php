<?php

namespace App\Http\Controllers;

use App\DataTables\LeadNotesDataTable;
use App\Helper\Reply;
use App\Http\Requests\Lead\StoreLeadNote;
use App\Models\LeadNote;
use App\Models\LeadUserNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LeadNoteController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.notes';
        $this->middleware(function ($request, $next) {
            return $next($request);
        });
    }

    /**
     * Display a listing of lead notes using a DataTable.
     * Validates user permissions before rendering the notes index view.
     *
     * @param \App\DataTables\LeadNotesDataTable $dataTable
     * @return mixed
     */
    public function index(LeadNotesDataTable $dataTable)
    {
        abort_403(!(in_array(user()->permission('view_lead_note'), ['all', 'added'])));

        return $dataTable->render('lead-contact.notes.index', $this->data);
    }

    /**
     * Show the form for creating a new lead note.
     * Validates user permissions and retrieves employees for the form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        abort_403(!in_array(user()->permission('add_lead_note'), ['all', 'added', 'both']));

        $this->employees = User::allEmployees();
        $this->pageTitle = __('app.addLeadNote');
        $this->leadId = request('lead');
        $this->view = 'lead-contact.notes.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('lead-contact.create', $this->data);
    }

    /**
     * Display a specific lead note.
     * Validates user permissions based on note ownership or membership.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $this->note = LeadNote::findOrFail($id);
        $this->noteMembers = $this->note->members->pluck('user_id')->toArray();
        $this->employees = User::whereIn('id', $this->noteMembers)->get();

        $viewClientNotePermission = user()->permission('view_lead_note');
        $memberIds = $this->note->members->pluck('user_id')->toArray();

        abort_403(!($viewClientNotePermission == 'all'
            || ($viewClientNotePermission == 'added' && $this->note->added_by == user()->id)
            || ($viewClientNotePermission == 'owned' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
            || ($viewClientNotePermission == 'both' && (in_array(user()->id, $memberIds) || $this->note->added_by == user()->id))
        ));

        $this->pageTitle = __('app.lead') . ' ' . __('app.note');
        $this->view = 'lead-contact.notes.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('lead-contact.create', $this->data);
    }

    /**
     * Store a new lead note in storage.
     * Validates permissions, saves the note, and associates users if the note is private.
     *
     * @param \App\Http\Requests\Lead\StoreLeadNote $request
     * @return \App\Helper\Reply
     */
    public function store(StoreLeadNote $request)
    {
        abort_403(!in_array(user()->permission('add_lead_note'), ['all', 'added', 'both']));

        $this->employees = User::allEmployees();

        $note = new LeadNote();
        $note->title = $request->title;
        $note->lead_id = $request->lead_id;
        $note->details = $request->details;
        $note->type = $request->type;
        $note->ask_password = $request->ask_password ? $request->ask_password : '';
        $note->save();

        if ($request->type == 1) {
            $users = $request->user_id;

            if (!is_null($users)) {
                foreach ($users as $user) {
                    LeadUserNote::firstOrCreate([
                        'user_id' => $user,
                        'lead_note_id' => $note->id
                    ]);
                }
            }
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('lead-contact.show', $note->lead_id) . '?tab=notes']);
    }

    /**
     * Show the form for editing a lead note.
     * Validates user permissions based on note ownership or membership.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $this->pageTitle = __('app.editLeadNote');
        $this->note = LeadNote::findOrFail($id);
        $editClientNotePermission = user()->permission('view_lead_note');
        $memberIds = $this->note->members->pluck('user_id')->toArray();

        abort_403(!($editClientNotePermission == 'all'
            || ($editClientNotePermission == 'added' && user()->id == $this->note->added_by)
            || ($editClientNotePermission == 'owned' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
            || ($editClientNotePermission == 'both' && ($this->note->added_by == user()->id || in_array(user()->id, $memberIds)))
        ));

        $this->employees = User::allEmployees();
        $this->noteMembers = $this->note->members->pluck('user_id')->toArray();
        $this->leadId = $this->note->lead_id;
        $this->view = 'lead-contact.notes.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('lead-contact.create', $this->data);
    }

    /**
     * Update an existing lead note in storage.
     * Validates permissions, updates note details, and manages user associations for private notes.
     *
     * @param \App\Http\Requests\Lead\StoreLeadNote $request
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function update(StoreLeadNote $request, $id)
    {
        $note = LeadNote::findOrFail($id);
        $note->title = $request->title;
        $note->details = $request->details;
        $note->type = $request->type;
        $note->ask_password = $request->ask_password ?: '';
        $note->save();

        if ($request->type == 1) {
            LeadUserNote::where('lead_note_id', $note->id)->delete();

            $users = $request->user_id;

            if (!is_null($users)) {
                foreach ($users as $user) {
                    LeadUserNote::firstOrCreate([
                        'user_id' => $user,
                        'lead_note_id' => $note->id
                    ]);
                }
            }
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('lead-contact.show', $note->lead_id) . '?tab=notes']);
    }

    /**
     * Delete a lead note from storage.
     * Validates user permissions based on note ownership or membership before deletion.
     *
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function destroy($id)
    {
        $this->note = LeadNote::findOrFail($id);
        $this->deletePermission = user()->permission('delete_lead_note');
        $memberIds = $this->note->members->pluck('user_id')->toArray();

        abort_403(!($this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $this->note->added_by == user()->id)
            || ($this->deletePermission == 'owned' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
            || ($this->deletePermission == 'both' && ($this->note->added_by == user()->id || in_array(user()->id, $memberIds)))
        ));

        $this->note->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Perform bulk actions on lead notes.
     * Currently supports bulk deletion with appropriate permission checks.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Helper\Reply
     */
    public function applyQuickAction(Request $request)
    {
        if ($request->action_type == 'delete') {
            $this->deleteRecords($request);
            return Reply::success(__('messages.deleteSuccess'));
        }

        return Reply::error(__('messages.selectAction'));
    }

    /**
     * Delete multiple lead notes based on provided IDs.
     * Validates user permissions before performing bulk deletion.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function deleteRecords($request)
    {
        abort_403(!(user()->permission('delete_lead_note') == 'all'));

        LeadNote::whereIn('id', explode(',', $request->row_ids))->delete();
        return true;
    }

    /**
     * Display the password verification form for a lead note.
     * Renders the view to verify password access for restricted notes.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function askForPassword($id)
    {
        $this->note = LeadNote::findOrFail($id);
        return view('lead-contact.notes.verify-password', $this->data);
    }

    /**
     * Verify the provided password for accessing a restricted lead note.
     * Compares the input password with the user's stored password.
     *
     * @param \Illuminate\Http\Request $return
     * @return \App\Helper\Reply
     */
    public function checkPassword(Request $request)
    {
        $this->client = User::findOrFail($this->user->id);

        if (Hash::check($request->password, $this->client->password)) {
            return Reply::success(__('messages.passwordMatched'));
        }

        return Reply::error(__('messages.incorrectPassword'));
    }

}