<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\ProjectTemplate\StoreProjectCategory;
use App\Models\BaseModel;
use App\Models\ProjectCategory;

class ProjectCategoryController extends AccountBaseController
{

    /**
     * Show the form for creating a new project category.
     * Verifies add permission and retrieves all existing categories for the create view.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('manage_project_category');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->categories = ProjectCategory::all();
        return view('projects.create_category', $this->data);
    }

    /**
     * Store a new project category in the database.
     * Verifies add permission, saves the category name, and returns updated category options.
     *
     * @param  \App\Http\Requests\ProjectTemplate\StoreProjectCategory  $request
     * @return array
     */
    public function store(StoreProjectCategory $request)
    {
        $this->addPermission = user()->permission('manage_project_category');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $category = new ProjectCategory();
        $category->category_name = $request->category_name;
        $category->save();

        $categories = ProjectCategory::all();

        $options = BaseModel::options($categories, $category, 'category_name');

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }

    /**
     * Show the form for editing an existing project category.
     * Retrieves the specified category and renders the edit view.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->projectCategory = ProjectCategory::findOrFail($id);

        return view('project-settings.edit-category', $this->data);
    }

    /**
     * Update an existing project category in the database.
     * Updates the category name and returns updated category options.
     *
     * @param  \App\Http\Requests\ProjectTemplate\StoreProjectCategory  $request
     * @param  int  $id
     * @return array
     */
    public function update(StoreProjectCategory $request, $id)
    {
        $category = ProjectCategory::findOrFail($id);
        $category->category_name = strip_tags($request->category_name);
        $category->save();

        $categories = ProjectCategory::all();
        $options = BaseModel::options($categories, null, 'category_name');

        return Reply::successWithData(__('messages.updateSuccess'), ['data' => $options]);
    }

    /**
     * Delete a specific project category from the database.
     * Removes the category and returns updated category options.
     *
     * @param  int  $id
     * @return array
     */
    public function destroy($id)
    {
        ProjectCategory::destroy($id);
        $categories = ProjectCategory::all();
        $options = BaseModel::options($categories, null, 'category_name');

        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $options]);
    }

}