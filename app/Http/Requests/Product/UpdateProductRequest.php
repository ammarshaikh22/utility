<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdateProductRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * 
     * Returns true — meaning authorized users can update products.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules for updating a product.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required',             // Product name must be provided
            'price' => 'required|numeric',    // Price is required and must be numeric
            'downloadable_file' => 'nullable|file', // File is optional but must be valid if provided
        ];

        // Add any custom field validation rules
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Define custom attribute names for validation messages.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Include any custom field attributes
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }
}
