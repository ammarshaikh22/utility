<?php

namespace App\Http\Requests;

use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEstimate extends FormRequest
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
     * Prepare data before validation.
     */
    protected function prepareForValidation()
    {
        // Format estimate number if it's numeric
        if ($this->estimate_number && is_numeric($this->estimate_number)) {
            $this->merge([
                'estimate_number' => \App\Helper\NumberFormat::estimate($this->estimate_number),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'estimate_number' => [
                'required',
                // Ensure the estimate number is unique within the company
                Rule::unique('estimates')->where('company_id', company()->id)
                    ->when($this->route('estimate'), function ($q) {
                        $q->where('id', '<>', $this->route('estimate'));
                    })
            ],
            'client_id' => 'required',
            'valid_till' => 'required',
            'sub_total' => 'required',
            'total' => 'required',
            'currency_id' => 'required',
        ];

        // Include custom field rules
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Custom attribute names for validation errors.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Include custom field attributes
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'client_id.required' => __('modules.projects.selectClient')
        ];
    }

}
