<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrefixSetting extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool Returns true to allow all users to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * @return array<string, mixed> Returns an array of validation rules for the request inputs:
     * - Invoice-related fields: required if 'invoices' module is enabled, digits must be integer between 0 and 10 or nullable.
     * - Estimate-related fields: required if 'estimates' module is enabled, digits must be integer between 0 and 10 or nullable.
     * - Order-related fields: required if 'orders' module is enabled, digits must be integer between 0 and 10 or nullable.
     * - Proposal fields: always required, digits must be integer between 0 and 10 or nullable.
     */
    public function rules()
    {
        $rules = [];

        if(in_array('invoices', user_modules())){
            $rules['invoice_prefix'] = 'required';
            $rules['credit_note_prefix'] = 'required';
            $rules['invoice_digit'] = 'nullable|integer|min:0|max:10';
            $rules['credit_note_digit'] = 'nullable|integer|min:0|max:10';
        }

        if(in_array('estimates', user_modules())){
            $rules['estimate_prefix'] = 'required';
            $rules['estimate_digit'] = 'nullable|integer|min:0|max:10';
            $rules['estimate_request_prefix'] = 'required';
            $rules['estimate_request_digit'] = 'nullable|integer|min:0|max:10';
        }

        if(in_array('orders', user_modules())){
            $rules['order_prefix'] = 'required';
            $rules['order_digit'] = 'nullable|integer|min:0|max:10';
        }

        $rules['proposal_prefix'] = 'required';
        $rules['proposal_digit'] = 'nullable|integer|min:0|max:10';

        return $rules;
    }

}
