<?php

namespace App\Http\Requests\Admin\Client;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreClientRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

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
        // Custom validator to ensure the email does not belong to a superadmin
        \Illuminate\Support\Facades\Validator::extend('check_superadmin', function ($attribute, $value, $parameters, $validator) {
            return !\App\Models\User::withoutGlobalScopes([\App\Scopes\ActiveScope::class, \App\Scopes\CompanyScope::class])
                ->where('email', $value)
                ->where('is_superadmin', 1)
                ->exists();
        });

        $rules = [
            // Client name is required
            'name' => 'required',

            // Email is optional, must be valid, unique within the company, required if login is enabled, and must not be a superadmin email
            'email' => 'nullable|email:rfc,strict|required_if:login,enable|unique:users,email,null,id,company_id,' . company()->id . '|check_superadmin',

            // Slack username is optional
            'slack_username' => 'nullable',

            // Website is optional but must be a valid URL
            'website' => 'nullable|url',

            // Country is required if mobile is provided
            'country' => 'required_with:mobile',

            // Mobile is optional but must be numeric
            'mobile' => 'nullable|numeric'
        ];

        // Merge in any custom field validation rules
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Custom validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom message if the email belongs to a superadmin
            'email.check_superadmin' => __('superadmin.emailAlreadyExist'),

            // Custom message for invalid website URL format
            'website.url' => 'The website format is invalid. Add https:// or http to url'
        ];
    }

    /**
     * Custom attribute names for validation errors
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Include attributes for any custom fields
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }
}
