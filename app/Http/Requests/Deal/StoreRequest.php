<?php

namespace App\Http\Requests\Deal;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Validation rules for storing a new deal.
     *
     * @return array<string, string>
     */
    public function rules()
    {
        $rules = [];

        // Required core fields
        $rules['lead_contact'] = 'required';
        $rules['name'] = 'required';
        $rules['pipeline'] = 'required';
        $rules['stage_id'] = 'required';
        $rules['close_date'] = 'required';
        $rules['value'] = 'required';

        // Merge in any custom field validation rules
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Custom attribute names for validation messages.
     *
     * Helps in displaying human-friendly field names
     * instead of raw keys when validation fails.
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        $attributes = [];

        // Merge custom field attributes for dynamic fields
        $attributes = $this->customFieldsAttributes($attributes);

        // Human-friendly labels for validation messages
        $attributes['name'] = __('modules.deal.dealName');
        $attributes['stage_id'] = __('modules.deal.leadStages');

        return $attributes;
    }
}
