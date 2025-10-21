<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\CoreRequest;

class StoreProductCategory extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * 
     * This method returns `true`, meaning that any authorized user 
     * (already validated by middleware or authentication) 
     * can create a new product category.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for storing a new product category.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category_name' => 'required' // Category name must be provided when creating a product category.
        ];
    }
}
