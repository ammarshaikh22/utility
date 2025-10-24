<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Lead\StoreLeadCategory;
use App\Http\Requests\Lead\UpdateLeadCategory;
use App\Models\LeadCategory;

class LeadCategoryController extends AccountBaseController
{

    /**
     * Show the form for creating a new lead category.
     * Checks user permissions and retrieves existing categories for display.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $viewPermission = user()->permission('add_lead_category');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $this->categories = LeadCategory::all();
        return view('lead-settings.create-category-modal', $this->data);
    }

    /**
     * Store a new lead category in storage.
     * Validates user permissions, saves the category, and returns updated category options.
     *
     * @param \App\Http\Requests\Lead\StoreLeadCategory $request
     * @return \App\Helper\Reply
     */
    public function store(StoreLeadCategory $request)
    {
        $viewPermission = user()->permission('add_lead_category');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $category = new LeadCategory();
        $category->category_name = $request->category_name;
        $category->save();

        $categoryData = LeadCategory::all();
        $list = '<option value="">--</option>';

        foreach ($categoryData as $item) {
            $list .= '<option selected
                value="' . $item->id . '"> ' . $item->category_name . ' </option>';
        }

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $list]);
    }

    /**
     * Show the form for editing an existing lead category.
     * Validates user permissions before displaying the edit form.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $this->category = LeadCategory::findOrFail($id);
        $this->editPermission = user()->permission('edit_lead_category');
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->category->added_by == user()->id)));

        return view('lead-settings.edit-category-modal', $this->data);
    }

    /**
     * Update an existing lead category in storage.
     * Validates user permissions, updates the category name, and returns updated category data.
     *
     * @param \App\Http\Requests\Lead\UpdateLeadCategory $request
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function update(UpdateLeadCategory $request, $id)
    {
        $category = LeadCategory::findOrFail($id);
        $this->editPermission = user()->permission('edit_lead_category');
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->category->added_by == user()->id)));

        $category->category_name = $request->category_name;
        $category->save();

        $categoryData = LeadCategory::all();
        return Reply::successWithData(__('messages.recordSaved'), ['data' => $categoryData]);
    }

    /**
     * Delete a lead category from storage.
     * Validates user permissions before deletion and returns updated category data.
     *
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function destroy($id)
    {
        $category = LeadCategory::findOrFail($id);
        $this->deletePermission = user()->permission('delete_lead_category');

        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $category->added_by == user()->id)));

        LeadCategory::destroy($id);
        $categoryData = LeadCategory::all();
        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $categoryData]);
    }

    /**
     * Set a lead category as the default.
     * Resets the default status of other categories for the company and sets the specified category as default.
     *
     * @return \App\Helper\Reply
     */
    public function updateLeadCategory()
    {
        LeadCategory::where('is_default', 1)->where('company_id', company()->id)->update(['is_default' => 0]);
        
        $category = LeadCategory::findOrFail(request()->categoryId);
        $category->is_default = 1;
        $category->save();

        $categoryData = LeadCategory::all();
        return Reply::successWithData(__('messages.recordSaved'), ['data' => $categoryData]);
    }
}