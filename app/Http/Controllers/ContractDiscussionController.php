<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Admin\Contract\StoreDiscussionRequest;
use App\Http\Requests\Admin\Contract\UpdateDiscussionRequest;
use App\Models\ContractDiscussion;
use App\Helper\UserService;
use App\Models\ClientContact;

class ContractDiscussionController extends AccountBaseController
{
    /**
     * Constructor for the ContractDiscussionController.
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
     * Stores a new contract discussion comment.
     * Validates user permissions, creates a new discussion comment, and returns the updated discussion list view.
     *
     * @param StoreDiscussionRequest $request The validated request containing discussion data.
     * @return array JSON response with status and updated discussion list view.
     */
    public function store(StoreDiscussionRequest $request)
    {
        $this->addPermission = user()->permission('add_contract_discussion');
        // Restrict access if the user lacks appropriate permissions to add contract discussions
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->userId = UserService::getUserId();
        $this->cId = [];

        // If the user is a client, fetch associated client IDs
        if (in_array('client', user_roles()) && user()->is_client_contact == null) {
            $this->cId = ClientContact::where('user_id', user()->id)->pluck('client_id')->toArray();
        }

        // Create and save a new contract discussion comment
        $contractDiscussion = new ContractDiscussion();
        $contractDiscussion->from = $this->userId;
        $contractDiscussion->message = $request->comment;
        $contractDiscussion->contract_id = $request->contract_id;
        $contractDiscussion->save();

        // Fetch updated discussion list for the contract
        $this->discussions = ContractDiscussion::with('user')->where('contract_id', $request->contract_id)->orderByDesc('id')->get();
        $view = view('contracts.discussions.show', $this->data)->render();

        // Return data-only response with the updated discussion list view
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    /**
     * Displays the form to edit an existing contract discussion comment.
     * Validates user permissions and retrieves the specified comment.
     * Renders the edit discussion view.
     *
     * @param int $id The ID of the contract discussion comment to edit.
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        // Fetch the contract discussion comment with associated user
        $this->comment = ContractDiscussion::with('user')->findOrFail($id);
        $this->editPermission = user()->permission('edit_contract_discussion');
        $this->userId = UserService::getUserId();
        $this->cId = [];

        // If the user is a client, fetch associated client IDs
        if (in_array('client', user_roles()) && user()->is_client_contact == null) {
            $this->cId = ClientContact::where('user_id', user()->id)->pluck('client_id')->toArray();
        }

        // Restrict access based on user permissions
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && ($this->comment->added_by == user()->id || $this->comment->added_by == $this->userId || in_array($this->comment->added_by, $this->cId)))));

        // Render the edit discussion view
        return view('contracts.discussions.edit', $this->data);
    }

    /**
     * Updates an existing contract discussion comment.
     * Validates user permissions, updates the comment, and returns the updated discussion list view.
     *
     * @param UpdateDiscussionRequest $request The validated request containing updated discussion data.
     * @param int $id The ID of the contract discussion comment to update.
     * @return array JSON response with status and updated discussion list view.
     */
    public function update(UpdateDiscussionRequest $request, $id)
    {
        // Fetch the contract discussion comment
        $comment = ContractDiscussion::findOrFail($id);
        $this->editPermission = user()->permission('edit_contract_discussion');
        $this->userId = UserService::getUserId();
        $this->cId = [];

        // If the user is a client, fetch associated client IDs
        if (in_array('client', user_roles()) && user()->is_client_contact == null) {
            $this->cId = ClientContact::where('user_id', user()->id)->pluck('client_id')->toArray();
        }

        // Restrict access based on user permissions
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && ($comment->added_by == user()->id || $comment->added_by == $this->userId || in_array($comment->added_by, $this->cId)))));

        // Update the comment message
        $comment->message = $request->comment;
        $comment->save();

        // Fetch updated discussion list for the contract
        $this->discussions = ContractDiscussion::with('user')->where('contract_id', $comment->contract_id)->orderByDesc('id')->get();
        $view = view('contracts.discussions.show', $this->data)->render();

        // Return data-only response with the updated discussion list view
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    /**
     * Deletes a contract discussion comment.
     * Validates user permissions, removes the specified comment, and returns the updated discussion list view.
     *
     * @param int $id The ID of the contract discussion comment to delete.
     * @return array JSON response with status and updated discussion list view.
     */
    public function destroy($id)
    {
        // Fetch the contract discussion comment
        $comment = ContractDiscussion::findOrFail($id);
        $this->deletePermission = user()->permission('delete_contract_discussion');
        $this->userId = UserService::getUserId();
        $this->cId = [];

        // If the user is a client, fetch associated client IDs
        if (in_array('client', user_roles()) && user()->is_client_contact == null) {
            $this->cId = ClientContact::where('user_id', user()->id)->pluck('client_id')->toArray();
        }

        // Restrict access based on user permissions
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && ($comment->added_by == user()->id || $comment->added_by == $this->userId || in_array($comment->added_by, $this->cId)))));

        // Store the contract ID before deletion
        $comment_contract_id = $comment->contract_id;
        // Delete the comment
        $comment->delete();

        // Fetch updated discussion list for the contract
        $this->discussions = ContractDiscussion::with('user')->where('contract_id', $comment_contract_id)->orderByDesc('id')->get();
        $view = view('contracts.discussions.show', $this->data)->render();

        // Return data-only response with the updated discussion list view
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }
}