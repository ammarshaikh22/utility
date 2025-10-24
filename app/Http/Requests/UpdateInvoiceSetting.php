<?php

namespace App\Http\Requests;

class UpdateInvoiceSetting extends CoreRequest
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
     * @return array Returns an array of validation rules for the request inputs:
     * - 'due_after': required and must be a numeric value.
     * - 'invoice_terms': required field.
     */
    public function rules()
    {
        $rules = [
            'due_after' => 'required|numeric',
            'invoice_terms' => 'required'
        ];

        return $rules;
    }

}
