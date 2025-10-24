<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\Reply;
use App\Http\Requests\Admin\Client\StoreClientSubcategory;
use App\Models\ClientCategory;
use App\Models\ClientSubCategory;

class ClientSubCategoryController extends AccountBaseController
{
    /**
     * Displays the form for creating a new client subcategory.
     * Retrieves all subcategories, categories, and user permissions for managing subcategories.
     * Renders the create subcategory view.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        // Fetch all client subcategories and categories
        $this->subcategories = ClientSubCategory::all();
        $this->categories = ClientCategory::all();
        
        // Retrieve the user's permission to manage client subcategories
        $this->deletePermission = user()->permission('manage_client_subcategory');

        // Render the create client subcategory view
        return view('clients.create-subcategory', $this->data);
    }

    /**
     * Stores a new client subcategory.
     * Validates the input using the StoreClientSubcategory request, creates a new subcategory, and returns the updated subcategory list for the selected category.
     *
     * @param StoreClientSubcategory $request The validated request containing subcategory data.
     * @return array JSON response with success message and updated subcategory data.
     */
    public function store(StoreClientSubcategory $request)
    {
        // Create and save a new client subcategory
        $category = new ClientSubCategory();
        $category->category_id = $request->category_id;
        $category->category_name = $request->category_name;
        $category->save();

        // Fetch all subcategories for the selected category
        $categories = ClientSubCategory::where('category_id', $request->selected_category)->get();

        // Return success response with updated subcategory data
        return Reply::successWithData(__('messages.recordSaved'), ['data' => $categories]);
    }

    /**
     * Updates an existing client subcategory.
     * Validates user permissions, updates the specified subcategory, and returns the updated subcategory list for the selected category.
     *
     * @param Request $request The request containing the updated subcategory name and selected category.
     * @param int $id The ID of the client subcategory to update.
     * @return array JSON response with success message and updated subcategory data.
     */
    public function update(Request $request, $id)
    {
        // Restrict access if the user does not have 'all' permission to manage client subcategories
        abort_403(user()->permission('manage_client_subcategory') != 'all');

        // Fetch and update the client subcategory
        $category = ClientSubCategory::findOrFail($id);
        $category->category_name = strip_tags($request->category_name);
        $category->save();

        // Fetch all subcategories for the selected category
        $categoryData = ClientSubCategory::where('category_id', $request->selectedCategory)->get();

        // Return success response with updated subcategory data
        return Reply::successWithData(__('messages.updateSuccess'), ['data' => $categoryData]);
    }

    /**
     * Deletes a client subcategory.
     * Validates user permissions, removes the specified subcategory, and returns the updated subcategory list for the selected category.
     *
     * @param Request $request The request containing the selected category.
     * @param int $id The ID of the client subcategory to delete.
     * @return array JSON response with success message and updated subcategory data.
     */
    public function destroy(Request $request, $id)
    {
        // Restrict access if the user does not have 'all' permission to manage client subcategories
        abort_403(user()->permission('manage_client_subcategory') != 'all');

        // Fetch and delete the client subcategory
        ClientSubCategory::findOrFail($id);
        ClientSubCategory::destroy($id);

        // Fetch all subcategories for the selected category
        $categoryData = ClientSubCategory::where('category_id', $request->selectedCategory)->get();

        // Return success response with updated subcategory data
        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $categoryData]);
    }

    /**
     * Retrieves subcategories for a given category ID.
     * Fetches all subcategories associated with the specified category and returns them as a JSON response.
     *
     * @param int $id The ID of the client category.
     * @return array JSON response with status and subcategory data.
     */
    public function getSubCategories($id)
    {
        // Fetch all subcategories for the specified category ID
        $sub_categories = ClientSubCategory::where('category_id', $id)->get();

        // Return data-only response with subcategories
        return Reply::dataOnly(['status' => 'success', 'data' => $sub_categories]);
    }
}