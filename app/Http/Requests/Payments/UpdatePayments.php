<?php

namespace App\Http\Requests\Payments;

use App\Http\Requests\CoreRequest;

class UpdatePayments extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Returns true — all authenticated users are allowed to update payments.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules for updating a payment record.
     * 
     * Ensures that the payment update request includes valid and 
     * consistent data for amount, date, and gateway information.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'amount' => 'required|numeric|min:1', // Payment amount is required and must be a positive number
            'paid_on' => 'required', // Payment date is mandatory
            'offline_methods' => 'required_if:gateway,==,Offline', // Offline method is required when gateway = "Offline"
        ];

        /**
         * --- Transaction ID Validation ---
         * Ensures that each transaction ID is unique within the `payments` table
         * for the current company, excluding the current record being updated.
         */
        if ($this->transaction_id) {
            $rules['transaction_id'] = 'unique:payments,transaction_id,' 
                . $this->route('payment') . ',id,company_id,' . company()->id;
        }

        return $rules;
    }

    /**
     * Define custom error messages for validation.
     * 
     * Provides a more specific message when the invoice ID is missing.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'invoice_id.required' => 'Select the invoice you want to add payment for.'
        ];
    }
}
