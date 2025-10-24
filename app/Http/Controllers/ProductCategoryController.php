<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Product\StoreProductCategory;
use App\Models\BaseModel;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;

class ProductCategoryController extends AccountBaseController
{

    /**
     * Show the form for creating a new product category.
     * Retrieves all existing categories and renders the create category view.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->categories = ProductCategory::all();
        return view('products.category.create', $this->data);
    }

    /**
     * Store a new product category in the database.
     * Saves the category name and returns updated category options and related subcategory data.
     *
     * @param  \App\Http\Requests\Product\StoreProductCategory  $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreProductCategory $request)
    {
        $category = new ProductCategory();
        $category->category_name = $request->category_name;
        $category->save();

        $categories = ProductCategory::get();
        $options = BaseModel::options($categories, $category, 'category_name');

        $subCategoryData = ProductSubCategory::where('category_id', $category->id)->get();
        return Reply::successWithData(__('messages.recordSaved'), ['data' => $options, 'subCategoryData' => $subCategoryData]);
    }

    /**
     * Update an existing product category in the database.
     * Updates the category name and returns updated category options.
     *
     * @param  \App\Http\Requests\Product\StoreProductCategory  $request
     * @param  int  $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(StoreProductCategory $request, $id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->category_name = strip_tags($request->category_name);
        $category->save();

        $categories = ProductCategory::get();
        $options = BaseModel::options($categories, null, 'category_name');

        return Reply::successWithData(__('messages.updateSuccess'), ['data' => $options]);
    }

    /**
     * Delete a specific product category from the database.
     * Removes the category and returns the updated list of categories.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ProductCategory::destroy($id);
        $categoryData = ProductCategory::all();
        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $categoryData]);
    }

}