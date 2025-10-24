<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class AccountSetupRequest extends FormRequest
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
        $global = global_setting();

        $rules = [
            // Company name is required
            'company_name' => 'required',

            // Full name of the user is required
            'full_name' => 'required',

            // Email is required and must be valid
            'email' => 'required|email:rfc,strict',

            // Password is required and must be at least 8 characters
            'password' => 'required|min:8',
        ];

        // Terms and conditions are required if enabled in global settings
        if ($global && $global->sign_up_terms == 'yes') {
            $rules['terms_and_conditions'] = 'required';
        }

        return $rules;
    }

}
