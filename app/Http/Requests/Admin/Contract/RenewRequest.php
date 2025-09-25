<?php

namespace App\Http\Requests\Admin\Contract;

use App\Http\Requests\CoreRequest;
use App\Models\Contract;

class RenewRequest extends CoreRequest
{

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
        $setting = company(); // Get current company settings

        $rules = [
            // Amount is required
            'amount' => 'required',

            // End date is optional, must match company date format, and cannot be before start_date
            'end_date' => 'nullable|date_format:"' . $setting->date_format . '"|after_or_equal:start_date',
        ];

        // If renewing an existing contract, validate start_date based on previous contract end_date
        if (request()->has('contract_id')) {
            $contract = Contract::findOrFail(request()->contract_id);
            $startDate = $contract->end_date 
                ? $contract->end_date->format($setting->date_format) 
                : $contract->start_date->format($setting->date_format);

            // Start date is required, must match date format, and cannot be before previous end date
            $rules['start_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:' . $startDate;
        }

        return $rules;
    }

    /**
     * Custom error messages for validation rules
     *
     * @return array
     */
    public function messages()
    {
        return [
            'amount.required' => 'The amount field is required.',
            'start_date.required' => 'The start date field is required.',
            'end_date.required' => 'The end date field is required.'
        ];
    }

}
