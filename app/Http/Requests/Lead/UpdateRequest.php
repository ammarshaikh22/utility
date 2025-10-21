<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

/**
 * Class UpdateRequest
 *
 * Handles validation when updating a lead record.
 * Ensures proper validation of client details and integrates
 * custom field validation dynamically.
 */
class UpdateRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * Always returns true, allowing any authenticated user
     * to update lead data. You can add authorization logic
     * later if role-based permissions are required.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules that apply to this request.
     *
     * @return array
     *
     * Validation rules:
     * - Extends the validator to include a custom rule `check_superadmin`
     *   which prevents using a super admin email for a lead.
     * - Ensures `client_name` is required.
     * - Ensures `client_email` (if provided) is a valid email format and unique
     *   within the `leads` table for the current company.
     * - Applies custom field rules via the `CustomFieldsRequestTrait`.
     */
    public function rules()
    {
        // Custom validation rule: disallow using Super Admin emails for leads
        \Illuminate\Support\Facades\Validator::extend('check_superadmin', function ($attribute, $value, $parameters, $validator) {
            return !\App\Models\User::withoutGlobalScopes([\App\Scopes\ActiveScope::class, \App\Scopes\CompanyScope::class])
                ->where('email', $value)
                ->where('is_superadmin', 1)
                ->exists();
        });

        $rules = [
            // Client name is required
            'client_name' => 'required',

            // Email must be valid, unique per company, and not used by a superadmin
            'client_email' => 'nullable|email:rfc,strict|unique:leads,client_email,' 
                . $this->route('lead_contact') . ',id,company_id,' . company()->id,
        ];

        // Merge dynamically generated custom field validation rules
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Define the display names of fields for validation error messages.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Add any custom field labels
        $attributes = $this->customFieldsAttributes($attributes);

        // Define readable labels for core fields
        $attributes['client_name'] = __('app.name');
        $attributes['client_email'] = __('app.email');

        return $attributes;
    }

    /**
     * Custom error messages for validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Message for when a superadmin email is used in a lead
            'client_email.check_superadmin' => __('superadmin.emailAlreadyExist'),
        ];
    }
}
