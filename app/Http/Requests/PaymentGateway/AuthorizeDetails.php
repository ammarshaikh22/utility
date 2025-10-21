<?php

namespace App\Http\Requests\PaymentGateway;

use Illuminate\Foundation\Http\FormRequest;

class AuthorizeDetails extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true to allow authorized users to proceed.
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
     * Validation ensures that all payment card details are provided correctly.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'card_number' => 'required|numeric', // Must be a valid numeric card number
            'expiration_month' => 'required',    // Card expiry month is required
            'expiration_year' => 'required',     // Card expiry year is required
            'cvv' => 'required|numeric|digits_between:3,4', // CVV must be 3–4 digits
        ];
    }

}
