<?php

namespace App\Http\Requests\User;

use App\Models\UserInvitation;
use Illuminate\Foundation\Http\FormRequest;

class AcceptInviteRequest extends FormRequest
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
        // Custom validator to ensure the email is not already used by a superadmin
        \Illuminate\Support\Facades\Validator::extend('check_superadmin', function ($attribute, $value, $parameters, $validator) {
            return !\App\Models\User::withoutGlobalScopes([\App\Scopes\ActiveScope::class, \App\Scopes\CompanyScope::class])
                ->where('email', $value)
                ->where('is_superadmin', 1)
                ->exists();
        });

        // Retrieve the active invitation based on the invite code
        $invite = UserInvitation::where('invitation_code', request()->invite)
            ->where('status', 'active')
            ->first();

        $rules = [
            // Name is required
            'name' => 'required',

            // Password is required and must be at least 8 characters
            'password' => 'required|min:8'
        ];

        // Email address is required if provided in the request
        if (request()->has('email_address')) {
            $rules['email_address'] = 'required';
        }

        // Terms and conditions are required if enabled in global settings
        $global = global_setting();
        if ($global && $global->sign_up_terms == 'yes') {
            $rules['terms_and_conditions'] = 'required';
        }

        // Email is required, must be valid, checked against superadmin, and unique within the company
        $rules['email'] = 'required|email:rfc,strict|check_superadmin|unique:users,email,null,id,company_id,' . $invite->company->id;

        return $rules;
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom message for email already existing as superadmin
            'email.check_superadmin' => __('superadmin.emailAlreadyExist'),
        ];
    }

}
