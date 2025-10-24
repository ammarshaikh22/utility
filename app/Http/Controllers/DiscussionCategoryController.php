<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\DiscussionCategory\StoreRequest;
use App\Http\Requests\DiscussionCategory\UpdateRequest;
use App\Models\DiscussionCategory;

/**
 * Class DiscussionCategoryController
 *
 * Handles CRUD operations for discussion categories,
 * including create, update, and delete functionality.
 */
class DiscussionCategoryController extends AccountBaseController
{
    /**
     * Show the form for creating a new discussion category.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->addPermission = user()->permission('manage_discussion_category');
        abort_403(!in_array($this->addPermission, ['all'])); // Restrict access if no permission

        $this->categories = DiscussionCategory::all();
        return view('discussions.create_category', $this->data);
    }

    /**
     * Store a newly created discussion category in storage.
     *
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreRequest $request)
    {
        $this->addPermission = user()->permission('manage_discussion_category');
        abort_403(!in_array($this->addPermission, ['all']));

        // Create new category
        $category = new DiscussionCategory();
        $category->name = $request->category_name;
        $category->color = $request->color;
        $category->save();

        // Refresh category list for dropdown options
        $categories = DiscussionCategory::all();
        $options = '<option value="">' . __('app.all') . '</option>';

        foreach ($categories as $item) {
            $options .= '<option data-content="<i class=\'fa fa-circle mr-2\' style=\'color: ' 
                . $item->color . '\'></i> ' . $item->name . '" value="' . $item->id . '"> ' 
                . $item->name . ' </option>';
        }

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }

    /**
     * Update the specified discussion category in storage.
     *
     * @param UpdateRequest $request
     * @param int $id
     * @return array
     */
    public function update(UpdateRequest $request, $id)
    {
        $category = DiscussionCategory::findOrFail($id);

        // Update fields if provided
        if ($request->has('name')) {
            $category->name = strip_tags($request->name);
        }

        if ($request->has('color')) {
            $category->color = strip_tags($request->color);
        }

        $category->save();

        // Refresh category list for dropdown options
        $categories = DiscussionCategory::all();
        $options = '<option value="">' . __('app.all') . '</option>';

        foreach ($categories as $item) {
            $options .= '<option data-content="<i class=\'fa fa-circle mr-2\' style=\'color: ' 
                . $item->color . '\'></i> ' . $item->name . '" value="' . $item->id . '"> ' 
                . $item->name . ' </option>';
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['data' => $options]);
    }

    /**
     * Remove the specified discussion category from storage.
     *
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        $this->addPermission = user()->permission('manage_discussion_category');
        abort_403($this->addPermission !== 'all'); // Only "all" permission can delete

        DiscussionCategory::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }
}
