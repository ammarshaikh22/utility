<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\CoreRequest;

class StoreProductSubCategory extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * 
     * Returns true, meaning any authorized user can create a product subcategory.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules for storing a new product subcategory.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category_id' => 'required',     // The parent category ID must be provided
            'category_name' => 'required',   // The subcategory name is mandatory
        ];
    }
}
