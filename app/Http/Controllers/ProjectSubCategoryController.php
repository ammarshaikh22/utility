<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helper\Reply;
use App\Http\Requests\Admin\Client\StoreClientSubcategory;
use App\Models\ClientCategory;
use App\Models\ClientSubCategory;
use App\Models\ProjectCategory;
use App\Models\ProjectSubCategory;

class ProjectSubCategoryController extends AccountBaseController
{

    /**
     * Show the form for creating a new project subcategory.
     * Verifies add permission and retrieves all project categories and subcategories for the create view.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->addPermission = user()->permission('manage_project_category');
        $this->deletePermission = user()->permission('manage_project_category');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->categories = ProjectCategory::all();
        $this->subcategories = ProjectSubCategory::all();
        return view('projects.create_sub_category', $this->data);
    }

    /**
     * Store a new project subcategory in the database.
     * Saves the subcategory with its associated category ID and returns the updated list of subcategories.
     *
     * @param  \App\Http\Requests\Admin\Client\StoreClientSubcategory  $request
     * @return array
     */
    public function store(StoreClientSubcategory $request)
    {
        $category = new ProjectSubCategory();
        $category->category_id = $request->category_id;
        $category->category_name = $request->category_name;
        $category->save();

        $categories = ProjectSubCategory::where('category_id', $request->selected_category)->get();

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $categories]);
    }

    /**
     * Update an existing project subcategory in the database.
     * Verifies update permission, updates the subcategory name, and returns the updated list of subcategories for the selected category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return array
     */
    public function update(Request $request, $id)
    {
        $this->addPermission = user()->permission('manage_project_category');
        $this->deletePermission = user()->permission('manage_project_category');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $category = ProjectSubCategory::findOrFail($id);

        $category->category_name = strip_tags($request->category_name);
        $category->save();

        $categoryData = ProjectSubCategory::where('category_id', $request->selectedCategory)->get();

        return Reply::successWithData(__('messages.updateSuccess'), ['data' => $categoryData]);
    }

    /**
     * Delete a specific project subcategory from the database.
     * Verifies delete permission, removes the subcategory, and returns the updated list of subcategories for the selected category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return array
     */
    public function destroy(Request $request, $id)
    {
        abort_403(user()->permission('manage_project_category') != 'all');

        ProjectSubCategory::findOrFail($id);

        ProjectSubCategory::destroy($id);
        $categoryData = ProjectSubCategory::where('category_id', $request->selectedCategory)->get();

        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $categoryData]);
    }

    /**
     * Retrieve subcategories for a specific project category.
     * Returns a list of subcategories associated with the given category ID.
     *
     * @param  int  $id
     * @return array
     */
    public function getSubCategories($id)
    {
        $sub_categories = ProjectSubCategory::where('category_id', $id)->get();

        return Reply::dataOnly(['status' => 'success', 'data' => $sub_categories]);
    }

}