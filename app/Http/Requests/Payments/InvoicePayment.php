<?php

namespace App\Http\Requests\Payments;

use App\Http\Requests\CoreRequest;

class InvoicePayment extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Always returns true — any authorized user can perform this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules for invoice payment.
     * Ensures that the offline payment method is provided.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'offlineMethod' => 'required', // Offline payment method must be selected
        ];
    }
}
