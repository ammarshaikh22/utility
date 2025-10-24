<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\Reply;
use App\Http\Requests\ClientContacts\StoreContact;
use App\Http\Requests\ClientContacts\UpdateContact;
use App\Models\ClientCategory;
use App\Models\ClientContact;
use App\Models\LanguageSetting;
use App\Models\Lead;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Enums\Salutation;
use App\Models\Notification;

class ClientContactController extends AccountBaseController
{
    /**
     * Constructor for the ClientContactController.
     * Initializes the parent controller, sets the page title, and applies middleware to restrict access to users with the clients module enabled.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.clients';
        $this->middleware(function ($request, $next) {
            // Restrict access if the clients module is not enabled for the user
            abort_403(!in_array('clients', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Displays the form to create a new client contact.
     * Validates user permissions to add client contacts, retrieves necessary data (countries, categories, salutations, languages), and renders the create view.
     *
     * @return \Illuminate\Contracts\View\View|array
     */
    public function create()
    {
        $this->pageTitle = __('app.addContact');
        $this->addClientPermission = user()->permission('add_client_contacts');

        // Restrict access if the user lacks appropriate permissions to add client contacts
        abort_403(!in_array($this->addClientPermission, ['all', 'added']));

        // Fetch data for the form
        $this->clientId = request('client');
        $this->countries = countries();
        $this->categories = ClientCategory::all();
        $this->salutations = Salutation::cases();
        $this->languages = LanguageSetting::where('status', 'enabled')->get();
        $this->view = 'clients.contacts.create';

        // Handle AJAX requests by rendering the create view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main create view
        return view('clients.create', $this->data);
    }

    /**
     * Stores a new client contact.
     * Validates the input using the StoreContact request, creates a new client contact, and redirects to the client details page.
     *
     * @param StoreContact $request The validated request containing client contact data.
     * @return array JSON response with success message and redirect URL.
     */
    public function store(StoreContact $request)
    {
        // Create and save a new client contact
        $contact = ClientContact::create($request->all());

        // Return success response with redirect to the client details page (contacts tab)
        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('clients.show', $contact->user_id) . '?tab=contacts']);
    }

    /**
     * Displays the details of a specific client contact.
     * Validates user permissions to view client contacts, retrieves the contact and client data, and renders the show view.
     *
     * @param int $id The ID of the client contact to display.
     * @return \Illuminate\Contracts\View\View|array
     */
    public function show($id)
    {
        // Fetch the client contact
        $this->contact = ClientContact::findOrFail($id);
        $this->pageTitle = __('app.showContact');

        // Retrieve user permissions
        $this->viewPermission = user()->permission('view_client_contacts');
        $this->editClientPermission = user()->permission('edit_client_contacts');
        $this->deleteClientPermission = user()->permission('delete_client_contacts');

        // Restrict access if the user lacks appropriate permissions
        abort_403(!($this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->contact->client->clientDetails->added_by == user()->id)
            || ($this->viewPermission == 'both' && $this->contact->client->clientDetails->added_by == user()->id)));

        // Fetch the associated client without the ActiveScope
        $this->client = User::withoutGlobalScope(ActiveScope::class)->findOrFail($this->contact->client_id);
        $this->view = 'clients.contacts.show';

        // Handle AJAX requests by rendering the show view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main show view
        return view('clients.create', $this->data);
    }

    /**
     * Displays the form to edit an existing client contact.
     * Validates user permissions, retrieves the contact, client, and necessary data (countries, categories, salutations, languages), and renders the edit view.
     *
     * @param int $id The ID of the client contact to edit.
     * @return \Illuminate\Contracts\View\View|array
     */
    public function edit($id)
    {
        $this->pageTitle = __('app.editContact');
        $this->contact = ClientContact::findOrFail($id);
        $this->client = User::withoutGlobalScope(ActiveScope::class)->with('clientDetails')->findOrFail($this->contact->client_id);

        $this->editPermission = user()->permission('edit_client_contacts');

        // Restrict access if the user lacks appropriate permissions
        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->contact->client->clientDetails->added_by == user()->id)
            || ($this->editPermission == 'both' && $this->contact->client->clientDetails->added_by == user()->id)));

        // Fetch data for the form
        $this->countries = countries();
        $this->categories = ClientCategory::all();
        $this->salutations = Salutation::cases();
        $this->languages = LanguageSetting::where('status', 'enabled')->get();
        $this->clientId = $this->contact->user_id;

        $this->view = 'clients.contacts.edit';

        // Handle AJAX requests by rendering the edit view
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        // Render the main edit view
        return view('clients.create', $this->data);
    }

    /**
     * Updates an existing client contact.
     * Validates the input using the UpdateContact request, updates the contact, and redirects to the client details page.
     *
     * @param UpdateContact $request The validated request containing updated client contact data.
     * @param int $id The ID of the client contact to update.
     * @return array JSON response with success message and redirect URL.
     */
    public function update(UpdateContact $request, $id)
    {
        // Fetch and update the client contact
        $contact = ClientContact::findOrFail($id);
        $contact->update($request->all());

        // Return success response with redirect to the client details page (contacts tab)
        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('clients.show', $contact->user_id) . '?tab=contacts']);
    }

    /**
     * Deletes a client contact.
     * Validates user permissions, deletes the contact and associated client data (if applicable), and updates related records.
     *
     * @param int $id The ID of the client contact to delete.
     * @return array JSON response with success message and redirect URL.
     */
    public function destroy($id)
    {
        $this->contact = ClientContact::findOrFail($id);
        $userID = $this->contact->user_id;
        $this->deletePermission = user()->permission('delete_client_contacts');

        // Check if the user has permission to delete the contact
        if (
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $this->contact->client->clientDetails->added_by == user()->id)
            || ($this->deletePermission == 'both' && $this->contact->client->clientDetails->added_by == user()->id)
        ) {
            // If the contact is linked to a client, delete related client data
            if (!is_null($this->contact->client_id)) {
                $client = User::withoutGlobalScope(ActiveScope::class)->with('clientDetails')->findOrFail($this->contact->client_id);

                // Delete related universal search entries
                $universalSearches = UniversalSearch::where('searchable_id', $client->id)->where('module_type', 'client')->get();
                if ($universalSearches) {
                    foreach ($universalSearches as $universalSearch) {
                        UniversalSearch::destroy($universalSearch->id);
                    }
                }

                // Delete related unread notifications
                Notification::whereNull('read_at')
                    ->where(function ($q) use ($client) {
                        $q->where('data', 'like', '{"id":' . $client->id . ',%')
                          ->orWhere('data', 'like', '%,"name":' . $client->name . ',%')
                          ->orWhere('data', 'like', '%,"user_one":' . $client->id . ',%')
                          ->orWhere('data', 'like', '%,"client_id":' . $client->id . '%');
                    })->delete();

                // Delete the client
                $client->delete();

                // Update leads to remove the client reference
                Lead::where('client_id', $client->id)->update(['client_id' => null]);
            }

            // Delete the client contact
            $this->contact->delete();
        }

        // Return success response with redirect to the client details page (contacts tab)
        $redirectUrl = route('clients.show', $userID) . '?tab=contacts';
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Applies bulk actions (currently only delete) to selected client contacts.
     * Delegates to the deleteRecords method for the delete action.
     *
     * @param Request $request The request containing the action type and selected contact IDs.
     * @return array JSON response with success or error message.
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
            case 'delete':
                // Delete selected client contacts
                $this->deleteRecords($request);
                return Reply::success(__('messages.deleteSuccess'));
            default:
                // Return error if no valid action is selected
                return Reply::error(__('messages.selectAction'));
        }
    }

    /**
     * Deletes multiple client contact records in bulk.
     * Validates user permissions and removes the specified contacts.
     *
     * @param Request $request The request containing the IDs of contacts to delete.
     * @return bool True if deletion is successful.
     */
    protected function deleteRecords($request)
    {
        // Restrict access if the user does not have 'all' permission to delete clients
        abort_403(user()->permission('delete_clients') !== 'all');

        // Delete the specified client contacts
        ClientContact::whereIn('id', explode(',', $request->row_ids))->delete();

        return true;
    }
}