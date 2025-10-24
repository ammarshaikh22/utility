<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Admin\Contract\RenewRequest;
use App\Models\Contract;
use App\Models\ContractRenew;
use App\Models\ContractSign;
use Illuminate\Http\Request;

class ContractRenewController extends AccountBaseController
{
    /**
     * Constructor for the ContractRenewController.
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
     * Stores a new contract renewal.
     * Validates the input, creates a new renewal record, updates the contract, and optionally removes customer signatures.
     * Returns the updated renewal history view.
     *
     * @param RenewRequest $request The validated request containing renewal data.
     * @return array JSON response with success message and updated renewal history view.
     */
    public function store(RenewRequest $request)
    {
        $id = $request->contract_id;
        $contract = Contract::findOrFail($id);

        // Create and save a new contract renewal
        $contractRenew = new ContractRenew();
        $contractRenew->amount = $request->amount;
        $contractRenew->renewed_by = $this->user->id;
        $contractRenew->contract_id = $id;
        $contractRenew->start_date = companyToYmd($request->start_date);
        $contractRenew->end_date = companyToYmd($request->end_date);
        $contractRenew->save();

        // Remove customer signatures if not keeping them
        if (!$request->keep_customer_signature) {
            ContractSign::where('contract_id', $contract->id)->delete();
        }

        // Update the contract with new renewal details
        $contract->amount = $contractRenew->amount;
        $contract->start_date = $contractRenew->start_date;
        $contract->end_date = $contractRenew->end_date;
        $contract->save();

        // Fetch updated contract with related data
        $this->contract = Contract::with('signature', 'client', 'client.clientDetails', 'files', 'renewHistory', 'renewHistory.renewedBy')->findOrFail($id);

        // Render the renewal history view
        $view = view('contracts.renew.renew_history', $this->data)->render();

        // Return success response with updated renewal history view
        return Reply::successWithData(__('messages.contractRenewSuccess'), ['view' => $view]);
    }

    /**
     * Displays the form to edit an existing contract renewal.
     * Validates user permissions and retrieves the specified renewal record.
     * Renders the edit renewal view.
     *
     * @param int $id The ID of the contract renewal to edit.
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        // Fetch the contract renewal
        $this->renew = ContractRenew::findOrFail($id);
        $this->editPermission = user()->permission('edit_contract');

        // Restrict access if the user lacks appropriate permissions to edit contracts
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->renew->added_by == user()->id)));

        // Render the edit renewal view
        return view('contracts.renew.edit', $this->data);
    }

    /**
     * Updates an existing contract renewal.
     * Validates the input, updates the renewal record, and returns the updated renewal history view.
     *
     * @param Request $request The request containing updated renewal data.
     * @param int $id The ID of the contract renewal to update.
     * @return array JSON response with success message and updated renewal history view.
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(Request $request, $id)
    {
        // Fetch and update the contract renewal
        $contractRenew = ContractRenew::findOrFail($id);
        $contractRenew->amount = $request->amount;
        $contractRenew->start_date = companyToYmd($request->start_date);
        $contractRenew->end_date = companyToYmd($request->end_date);
        $contractRenew->save();

        // Fetch updated contract with related data
        $this->contract = Contract::with('signature', 'client', 'client.clientDetails', 'files', 'renewHistory', 'renewHistory.renewedBy')->findOrFail($contractRenew->contract_id);

        // Render the renewal history view
        $view = view('contracts.renew.renew_history', $this->data)->render();

        // Return success response with updated renewal history view
        return Reply::successWithData(__('messages.contractRenewSuccess'), ['view' => $view]);
    }

    /**
     * Deletes a contract renewal.
     * Validates user permissions, removes the renewal record, updates the contract to reflect the previous or original state, and returns the updated renewal history view.
     *
     * @param int $id The ID of the contract renewal to delete.
     * @return array JSON response with success message and updated renewal history view.
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function destroy($id)
    {
        // Fetch the contract renewal
        $contractRenew = $this->renew = ContractRenew::findOrFail($id);
        $this->deletePermission = user()->permission('delete_contract');

        // Restrict access if the user lacks appropriate permissions to delete contracts
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $this->renew->added_by == user()->id)));

        // Check for the next renewal record
        $findNext = ContractRenew::where('created_at', '>', $contractRenew->created_at)->first();

        if (!$findNext) {
            // If no newer renewal exists, revert to previous or original contract details
            $findPrevious = ContractRenew::where('created_at', '<', $contractRenew->created_at)->latest()->first();
            $contract = Contract::findOrFail($contractRenew->contract_id);

            if ($findPrevious) {
                // Revert to previous renewal details
                $contract->start_date = $findPrevious->start_date;
                $contract->end_date = $findPrevious->end_date;
                $contract->amount = $findPrevious->amount;
            } else {
                // Revert to original contract details
                $contract->start_date = $contract->original_start_date;
                $contract->end_date = $contract->original_end_date;
                $contract->amount = $contract->original_amount;
            }

            $contract->save();
        }

        // Delete the renewal record
        ContractRenew::destroy($id);

        // Fetch updated contract with related renewal history
        $this->contract = Contract::with('renewHistory', 'renewHistory.renewedBy')->findOrFail($this->renew->contract_id);
        $view = view('contracts.renew.renew_history', $this->data)->render();

        // Return success response with updated renewal history view
        return Reply::successWithData(__('messages.deleteSuccess'), ['view' => $view]);
    }
}