<?php

namespace App\Http\Requests\Admin\Contract;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdateRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $setting = company(); // Get the current company's settings

        // Define validation rules for contract update
        $rules = [
            'client_id' => 'required', // Client must be selected
            'subject' => 'required',   // Contract subject is required
            'amount' => 'required',    // Contract amount is required
            'contract_type' => 'required|exists:contract_types,id', // Must be a valid contract type
            'start_date' => 'required|date_format:"' . $setting->date_format . '"', // Start date must match company date format
        ];

        // Merge custom field rules if there are any
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Get custom attributes for validation errors.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Include custom field attributes if present
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }
}
