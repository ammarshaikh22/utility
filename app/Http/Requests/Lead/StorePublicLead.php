<?php

namespace App\Http\Requests\Lead;

use App\Models\Company;
use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Support\Facades\Validator;

class StorePublicLead extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * Returning true allows any visitor or user (depending on the route)
     * to submit a public lead form. Authorization can be restricted
     * later if needed.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for storing a public lead.
     *
     * Includes:
     * - A custom validation rule (`check_superadmin`) that ensures
     *   the submitted email does not belong to a superadmin user.
     * - Email validation ensuring uniqueness across both `leads`
     *   and `users` tables for the same company.
     * - Recaptcha validation if enabled in global settings.
     *
     * @return array
     */
    public function rules()
    {
        // ✅ Add custom validator: disallow superadmin emails
        Validator::extend('check_superadmin', function ($attribute, $value, $parameters, $validator) {
            return !\App\Models\User::withoutGlobalScopes([
                \App\Scopes\ActiveScope::class,
                \App\Scopes\CompanyScope::class
            ])
            ->where('email', $value)
            ->where('is_superadmin', 1)
            ->exists();
        });

        // ✅ Get the company based on the submitted company_id
        $company = Company::findOrFail($this->request->get('company_id'));

        // ✅ Base validation rules
        $rules = [
            'name' => 'required',
            'email' => 'nullable|email:rfc,strict|check_superadmin'
                . '|unique:leads,client_email,null,id,company_id,' . $company->id
                . '|unique:users,email,null,id,company_id,' . $company->id,
        ];

        // ✅ Add rules for any custom fields defined in the system
        $rules = $this->customFieldRules($rules);

        // ✅ Google reCAPTCHA validation if enabled in global settings
        if (
            global_setting()->google_recaptcha_status == 'active' &&
            global_setting()->ticket_form_google_captcha == 1 &&
            global_setting()->google_recaptcha_v2_status == 'active'
        ) {
            $rules['g-recaptcha-response'] = 'required';
        }

        return $rules;
    }

    /**
     * Define human-readable names for attributes.
     *
     * This method also merges in any custom field attributes,
     * allowing them to appear properly in validation error messages.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    /**
     * Define custom validation messages.
     *
     * - `email.check_superadmin`: Custom message if a superadmin email
     *   is used in the form.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.check_superadmin' => __('superadmin.emailCantUse'),
        ];
    }
}
