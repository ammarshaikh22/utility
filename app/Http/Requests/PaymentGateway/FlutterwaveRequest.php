<?php

namespace App\Http\Requests\PaymentGateway;

use Illuminate\Foundation\Http\FormRequest;

class FlutterwaveRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true to allow authorized access.
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
     * Ensures that name and a valid email address are provided.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',                  // Name field is required
            'email' => 'required|email:rfc,strict' // Must be a valid email format
        ];
    }

}
