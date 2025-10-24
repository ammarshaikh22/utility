<?php

namespace App\Http\Requests\SuperAdmin\StripePayment;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Allows all users to make this request by returning true.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Ensures that a payment method is selected before processing a Stripe payment.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Payment method is required for processing
            'payment_method' => 'required',
        ];
    }
}
