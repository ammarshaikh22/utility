<?php

namespace App\Http\Requests\SuperAdmin\Register;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
     * Defines the rules for registering a new client under a company,
     * including custom validation to prevent duplicate superadmin emails.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // Custom validator to check that the email does not belong to an existing superadmin
        \Illuminate\Support\Facades\Validator::extend('check_superadmin', function ($attribute, $value, $parameters, $validator) {
            return !\App\Models\User::withoutGlobalScopes([\App\Scopes\ActiveScope::class, \App\Scopes\CompanyScope::class])
                ->where('email', $value)
                ->where('is_superadmin', 1)
                ->exists();
        });

        // Find the company using the provided hash, or fail if not found
        $company = Company::where('hash', request()->company_hash)->firstOrFail();

        // Retrieve global settings (for checking if terms are required)
        $global = global_setting();

        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255', // Name is required, string, max 255 chars
            // Email must be unique for the company and pass custom superadmin check
            'email' => 'required|email:rfc|check_superadmin|unique:users,email,null,id,company_id,' . $company->id,
            'password' => 'required|min:8', // Password is required, min 8 chars
        ];

        // Conditionally require terms and conditions if enabled in global settings
        if ($global && $global->sign_up_terms == 'yes') {
            $rules['terms_and_conditions'] = 'required';
        }

        return $rules;
    }

    /**
     * Custom messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            // Custom error message if the email is already used by a superadmin
            'email.check_superadmin' => __('superadmin.emailAlreadyExist'),
        ];
    }
}
