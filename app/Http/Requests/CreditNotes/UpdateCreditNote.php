<?php

namespace App\Http\Requests\CreditNotes;

use App\Http\Requests\CoreRequest;

class UpdateCreditNote extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users (can later be restricted by roles/permissions if needed)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            // These fields are always required for updating a credit note
            'issue_date' => 'required',
            'sub_total' => 'required',
            'total' => 'required',
        ];

        // If credit note has recurring payments enabled, 
        // enforce billing-related fields as required
        if ($this->recurring_payment == 'yes') {
            $rules['billing_frequency'] = 'required';     // e.g. weekly, monthly
            $rules['billing_interval'] = 'required|integer'; // e.g. every 1, 2, 3 units
            $rules['billing_cycle'] = 'required|integer'; // total number of cycles
        }

        return $rules;
    }
}
