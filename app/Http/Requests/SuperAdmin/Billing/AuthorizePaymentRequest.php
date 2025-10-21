<?php

namespace App\Http\Requests\SuperAdmin\Billing;

use Illuminate\Foundation\Http\FormRequest;

class AuthorizePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Any authorized user can make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'owner'       => 'required', // Card owner name must be provided
            'cvv'         => 'required', // CVV must be provided
            'card_number' => 'required', // Card number must be provided
        ];
    }
}
