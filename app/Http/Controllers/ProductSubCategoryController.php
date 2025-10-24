<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Product\StoreProductSubCategory;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use Illuminate\Http\Request;

class ProductSubCategoryController extends AccountBaseController
{

    /**
     * Show the form for creating a new product subcategory.
     * Retrieves categories, subcategories, and a specific category ID (if provided) to render the create view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->categoryID = $request->catID;
        $this->subcategories = ProductSubCategory::all();
        $this->categories = ProductCategory::all();

        return view('products.sub-category.create', $this->data);
    }

    /**
     * Store a new product subcategory in the database.
     * Saves the subcategory, generates dropdown options for categories and subcategories, and returns them.
     *
     * @param  \App\Http\Requests\Product\StoreProductSubCategory  $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreProductSubCategory $request)
    {
        $category = new ProductSubCategory();
        $category->category_id = $request->category_id;
        $category->category_name = $request->category_name;
        $category->save();

        $categoryData = ProductCategory::get();
        $category = '';
        $subCategory = '';
        $categoryID = $request->categoryID;
        $subCategoryData = ProductSubCategory::where('category_id', $categoryID)->get();

        foreach ($categoryData as $data) {
            $selected = ($categoryID == $data->id) ? 'selected' : '';
            $category .= '<option value='.$data->id.' '.$selected.'>'. $data->category_name .' </option>';
        }

        if ($categoryID) {
            foreach ($subCategoryData as $item) {
                $selected = ($categoryID == $item->category_id) ? 'selected' : '';
                $subCategory .= '<option value='.$item->id.' '. $selected .'>'. $item->category_name .' </option>';
            }
        }

        return Reply::successWithData(__('messages.recordSaved'), ['data' => $category, 'subCategoryData' => $subCategory]);
    }

    /**
     * Update an existing product subcategory in the database.
     * Updates the subcategory's category ID and name, and returns updated dropdown options for categories and subcategories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = ProductSubCategory::findOrFail($id);
        $category->category_id = $request->category_id ? $request->category_id : $category->category_id;
        $category->category_name = $request->category_name ? strip_tags($request->category_name) : $category->category_name;
        $category->save();

        $subCategoryOptions = $this->categoryDropdown($category->category_id);
        $categoryOptions = $this->subCategoryDropdown($category->id);

        return Reply::successWithData(__('messages.updateSuccess'), ['sub_categories' => $subCategoryOptions, 'categories' => $categoryOptions]);
    }

    /**
     * Delete a specific product subcategory from the database.
     * Removes the subcategory record and returns a success message.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ProductSubCategory::destroy($id);
        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Generate HTML options for a category dropdown.
     * Returns a list of categories with an optional selected category ID.
     *
     * @param  int|null  $selectId
     * @return string
     */
    public function categoryDropdown($selectId = null)
    {
        /* Category Dropdown */
        $categoryData = ProductCategory::get();
        $categoryOptions = '<option value="">--</option>';

        foreach ($categoryData as $item) {
            $selected = '';

            if (!is_null($selectId) && $item->id == $selectId) {
                $selected = 'selected';
            }

            $categoryOptions .= '<option ' . $selected . ' value="' . $item->id . '"> ' . $item->category_name . ' </option>';
        }

        return $categoryOptions;
    }

    /**
     * Generate HTML options for a subcategory dropdown.
     * Returns a list of subcategories with an optional selected subcategory ID.
     *
     * @param  int  $selectId
     * @return string
     */
    public function subCategoryDropdown($selectId)
    {
        /* Sub-Category Dropdown */
        $subCategoryData = ProductSubCategory::get();
        $subCategoryOptions = '<option value="">--</option>';

        foreach ($subCategoryData as $item) {
            $selected = '';

            if ($item->id == $selectId) {
                $selected = 'selected';
            }

            $subCategoryOptions .= '<option ' . $selected . ' value="' . $item->id . '"> ' . $item->category_name . ' </option>';
        }

        return $subCategoryOptions;
    }

    /**
     * Retrieve subcategories for a specific category ID.
     * Returns a list of subcategories associated with the given category.
     *
     * @param  int  $id
     * @return array
     */
    public function getSubCategories($id)
    {
        $sub_categories = ProductSubCategory::where('category_id', $id)->get();

        return Reply::dataOnly(['status' => 'success', 'data' => $sub_categories]);
    }

}