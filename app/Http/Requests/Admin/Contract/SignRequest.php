<?php

namespace App\Http\Requests\Admin\Contract;

use Illuminate\Foundation\Http\FormRequest;

class SignRequest extends FormRequest
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
        // Base rules for the signer information
        $rules = [
            'first_name' => 'required',               // First name is required
            'last_name'  => 'required',               // Last name is required
            'place'      => 'required',               // Place is required
            'email'      => 'required|email:rfc,strict', // Email is required and must be valid
        ];

        // Conditional rules based on the type of signature
        if (request('signature_type') == 'upload') {
            // If the user uploads a signature image
            $rules['image'] = 'required';
        } else {
            // If the signature is drawn or captured digitally
            $rules['signature'] = 'required';
        }

        return $rules;
    }

}
