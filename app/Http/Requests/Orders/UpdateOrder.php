<?php

namespace App\Http\Requests\Orders;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdateOrder extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true, meaning any authenticated user
     * can perform this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Validation details:
     * - Ensures sub_total and total fields are required.
     * - Automatically sets 'show_shipping_address' to 'yes' or 'no'.
     * - Includes dynamic validation rules for custom fields.
     *
     * @return array
     */
    public function rules()
    {
        // Set default value for show_shipping_address if not present
        $this->has('show_shipping_address')
            ? $this->request->add(['show_shipping_address' => 'yes'])
            : $this->request->add(['show_shipping_address' => 'no']);

        // Base validation rules
        $rules = [
            'sub_total' => 'required',
            'total' => 'required',
        ];

        // Apply custom field rules if defined
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Define custom attributes for validation messages.
     *
     * Merges system-defined and custom field attributes.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
