<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\Reply;
use App\Http\Requests\Admin\Client\StoreClientCategory;
use App\Models\ClientCategory;

class ClientCategoryController extends AccountBaseController
{
    /**
     * Displays the form for creating a new client category.
     * Retrieves all existing client categories and checks the user's permission to manage client categories.
     * Renders the create category view.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        // Fetch all client categories
        $this->categories = ClientCategory::all();
        
        // Retrieve the user's permission to manage client categories
        $this->deletePermission = user()->permission('manage_client_category');

        // Render the create client category view
        return view('clients.create_category', $this->data);
    }

    /**
     * Stores a new client category.
     * Validates the input using the StoreClientCategory request, creates a new category, and returns the updated category list.
     *
     * @param StoreClientCategory $request The validated request containing client category data.
     * @return array JSON response with success message and updated category data.
     */
    public function store(StoreClientCategory $request)
    {
        // Create and save a new client category
        $category = new ClientCategory();
        $category->category_name = strip_tags($request->category_name);
        $category->save();

        // Fetch all client categories after saving
        $categoryData = ClientCategory::all();

        // Return success response with updated category data
        return Reply::successWithData(__('messages.recordSaved'), ['data' => $categoryData]);
    }

    /**
     * Updates an existing client category.
     * Validates user permissions, updates the specified category, and returns the updated category list.
     *
     * @param Request $request The request containing the updated category name.
     * @param int $id The ID of the client category to update.
     * @return array JSON response with success message and updated category data.
     */
    public function update(Request $request, $id)
    {
        // Check user permission to manage client categories
        $this->editPermission = user()->permission('manage_client_category');
        // Restrict access if the user does not have 'all' permission
        abort_403($this->editPermission != 'all');

        // Fetch and update the client category
        $category = ClientCategory::findOrFail($id);
        $category->category_name = strip_tags($request->category_name);
        $category->save();

        // Fetch all client categories after updating
        $categoryData = ClientCategory::all();

        // Return success response with updated category data
        return Reply::successWithData(__('messages.updateSuccess'), ['data' => $categoryData]);
    }

    /**
     * Deletes a client category.
     * Validates user permissions, removes the specified category, and returns the updated category list.
     *
     * @param int $id The ID of the client category to delete.
     * @return array JSON response with success message and updated category data.
     */
    public function destroy($id)
    {
        // Check user permission to manage client categories
        $this->deletePermission = user()->permission('manage_client_category');
        // Restrict access if the user does not have 'all' permission
        abort_403($this->deletePermission != 'all');

        // Fetch and delete the client category
        $category = ClientCategory::findOrFail($id);
        $category->delete();

        // Fetch all client categories after deletion
        $categoryData = ClientCategory::all();

        // Return success response with updated category data
        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $categoryData]);
    }
}