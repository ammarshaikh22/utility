<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\BaseModel;
use App\Models\ContractType;
use App\Http\Requests\Admin\ContractType\StoreRequest;
use App\Http\Requests\Admin\ContractType\UpdateRequest;

class ContractTypeController extends AccountBaseController
{
    /**
     * Displays the form for creating a new contract type.
     * Validates user permissions and retrieves all existing contract types for display.
     * Renders the create contract type view.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->addPermission = user()->permission('manage_contract_type');

        // Restrict access if the user lacks appropriate permissions to manage contract types
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        // Fetch all contract types
        $this->categories = ContractType::all();

        // Render the create contract type view
        return view('contracts.types.create', $this->data);
    }

    /**
     * Stores a new contract type.
     * Validates user permissions and input using the StoreRequest, creates a new contract type, and returns the updated list of contract types as options.
     *
     * @param StoreRequest $request The validated request containing contract type data.
     * @return array JSON response with success message and updated contract type options.
     */
    public function store(StoreRequest $request)
    {
        $this->addPermission = user()->permission('manage_contract_type');
        // Restrict access if the user lacks appropriate permissions to manage contract types
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        // Create and save a new contract type
        $contract = new ContractType();
        $contract->name = $request->name;
        $contract->save();

        // Fetch all contract types and generate options for dropdown
        $categories = ContractType::all();
        $options = BaseModel::options($categories, $contract);

        // Return success response with updated contract type options
        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }

    /**
     * Updates an existing contract type.
     * Validates input using the UpdateRequest, updates the contract type name, and returns the updated list of contract types as options.
     *
     * @param UpdateRequest $request The validated request containing updated contract type data.
     * @param int $id The ID of the contract type to update.
     * @return array JSON response with success message and updated contract type options.
     */
    public function update(UpdateRequest $request, $id)
    {
        // Fetch and update the contract type
        $category = ContractType::findOrFail($id);
        $category->name = strip_tags($request->name);
        $category->save();

        // Fetch all contract types and generate options for dropdown
        $categories = ContractType::all();
        $options = BaseModel::options($categories);

        // Return success response with updated contract type options
        return Reply::successWithData(__('messages.updateSuccess'), ['data' => $options]);
    }

    /**
     * Deletes a contract type.
     * Validates user permissions, removes the specified contract type, and returns the updated list of contract types as options.
     *
     * @param int $id The ID of the contract type to delete.
     * @return array JSON response with success message and updated contract type options.
     */
    public function destroy($id)
    {
        // Restrict access if the user does not have 'all' permission to manage contract types
        abort_403(user()->permission('manage_contract_type') !== 'all');

        // Delete the contract type
        ContractType::destroy($id);

        // Fetch all contract types and generate options for dropdown
        $categories = ContractType::all();
        $options = BaseModel::options($categories);

        // Return success response with updated contract type options
        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $options]);
    }
}