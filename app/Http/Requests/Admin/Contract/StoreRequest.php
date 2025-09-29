<?php

namespace App\Http\Requests\Admin\Contract;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Validation\Rule;

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
        // Allow all users to make this request
        return true;
    }

    /**
     * Prepare data for validation before applying rules.
     */
    protected function prepareForValidation()
    {
        if ($this->contract_number) {
            // Format the contract number before validation
            $this->merge([
                'contract_number' => \App\Helper\NumberFormat::contract($this->contract_number),
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
        $setting = company(); // Get company settings (e.g., date format)

        $rules = [
            'contract_number' => [
                'required', // Contract number is required
                // Must be unique for the company
                Rule::unique('contracts')->where('company_id', company()->id)
            ],
            'client_id' => 'required', // Client ID is required
            'subject' => 'required', // Subject is required
            'amount' => 'required', // Contract amount is required
            'contract_type' => 'required|exists:contract_types,id', // Contract type must exist in DB
            'start_date' => 'required|date_format:"' . $setting->date_format . '"', // Start date with proper format
        ];

        // Merge any custom field rules
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Customize attribute names for validation messages.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Merge custom field attributes
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }
}
