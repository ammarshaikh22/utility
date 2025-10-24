<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\KnowledgeBaseCategory;
use App\Http\Requests\KnowledgeBase\KnowledgeBaseCategoryStore;
use App\Models\BaseModel;

class KnowledgeBaseCategoryController extends AccountBaseController
{

    /**
     * Display the form for creating a new knowledge base category.
     * Retrieves all existing categories for display in the form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->categories = KnowledgeBaseCategory::all();
        return view('knowledge-base.create_category', $this->data);
    }

    /**
     * Store a new knowledge base category in storage.
     * Validates and saves the category name, then returns updated category options.
     *
     * @param \App\Http\Requests\KnowledgeBase\KnowledgeBaseCategoryStore $request
     * @return \App\Helper\Reply
     */
    public function store(KnowledgeBaseCategoryStore $request)
    {
        $category = new KnowledgeBaseCategory();
        $category->name = strip_tags($request->category_name);
        $category->save();
        $categoryData = KnowledgeBaseCategory::all();
        $options = BaseModel::options($categoryData, $category, 'name');

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options]);
    }

    /**
     * Update an existing knowledge base category.
     * Validates and updates the category name, then returns updated category data.
     *
     * @param \App\Http\Requests\KnowledgeBase\KnowledgeBaseCategoryStore $request
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function update(KnowledgeBaseCategoryStore $request, $id)
    {
        $category = KnowledgeBaseCategory::findOrFail($id);
        $category->name = strip_tags($request->category_name);
        $category->save();

        $categoryData = KnowledgeBaseCategory::all();

        return Reply::successWithData(__('messages.updateSuccess'), ['data' => $categoryData]);
    }

    /**
     * Delete a knowledge base category from storage.
     * Removes the specified category and returns updated category data.
     *
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function destroy($id)
    {
        $category = KnowledgeBaseCategory::findOrFail($id);
        $category->delete();
        $categoryData = KnowledgeBaseCategory::all();
        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $categoryData]);
    }

}