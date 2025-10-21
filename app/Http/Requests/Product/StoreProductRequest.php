<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreProductRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * 
     * Returns true, meaning any authenticated or authorized user
     * can create a new product.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for storing a new product.
     *
     * @return array
     */
    public function rules()
    {
        // Default validation rules for product creation
        $rules = [
            'name' => 'required',                          // Product name is mandatory
            'price' => 'required|numeric',                 // Price must be provided and must be a valid number
            'downloadable_file' => 'required_if:downloadable,true|file', 
            // If the product is marked as downloadable, then a file must be uploaded
        ];

        // Include dynamic rules for any custom fields defined for this model
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Define custom error messages for validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom message when downloadable file is missing for a downloadable product
            'downloadable_file.required_if' => __('validation.required', ['attribute' => __('app.downloadableFile')]),
        ];
    }

    /**
     * Define custom attribute names for validation errors.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Merge any custom field attribute labels
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }
}
