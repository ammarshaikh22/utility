<?php

namespace App\Http\Requests\Deal;

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
        return true;
    }

    /**
     * Validation rules for updating an existing deal.
     *
     * @return array<string, string>
     */
    public function rules()
    {
        $rules = [];

        // Core required fields for update
        $rules['name'] = 'required';
        $rules['pipeline'] = 'required';
        $rules['stage_id'] = 'required';
        $rules['close_date'] = 'required';
        $rules['value'] = 'required';

        // Merge in dynamic custom field validation rules
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Custom attribute names for validation messages.
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        $attributes = [];

        // Add custom field attributes
        $attributes = $this->customFieldsAttributes($attributes);

        // Human-friendly field name for "name"
        $attributes['name'] = __('app.deal') . ' ' . __('app.name');

        return $attributes;
    }
}
