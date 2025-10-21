<?php

namespace App\Http\Requests\SuperAdmin\Register;

use App\Models\GlobalSetting;
use App\Models\User;
use App\Models\Company;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use Illuminate\Validation\Rule;
use App\Http\Requests\CoreRequest;
use Illuminate\Support\Facades\Validator;

class StoreRequest extends CoreRequest
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
     * Defines the rules for creating a new superadmin/company,
     * including subdomain validation, email uniqueness, password, 
     * terms acceptance, and Google reCAPTCHA validation.
     *
     * @return array
     */
    public function rules()
    {
        // Custom validator to prevent duplicate superadmin emails
        Validator::extend('check_superadmin', function ($attribute, $value, $parameters, $validator) {
            return !User::withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
                ->where('email', $value)
                ->where('is_superadmin', 1)
                ->exists();
        });

        // Subdomain validation if Subdomain module is enabled
        if (module_enabled('Subdomain')) {
            if (request()->sub_domain) {
                $subdomain = preg_replace('/\.' . preg_quote(getDomain(), '/') . '/', '', request()->sub_domain, 1);

                // If subdomain does not match regex, return the regex rule immediately
                if (!preg_match('/^[A-Z][a-zA-Z0-9]+$/i', $subdomain)) {
                    return [
                        'sub_domain' => 'regex:/^[A-Z][a-zA-Z0-9]+$/',
                    ];
                }
            }
        }

        $length = str(getDomain())->length();
        $min = $length + 1 + 4; // Minimum length for subdomain: domain length + dot + 4 chars

        // Base validation rules
        $rules = [
            'company_name' => 'required', // Company name is required
            'name' => 'required', // Superadmin name is required
            'email' => 'required|email:rfc,strict|check_superadmin', // Email validation with custom rule
            // Subdomain validation if module is enabled
            'sub_domain' => module_enabled('Subdomain') 
                ? 'required|banned_sub_domain|min:' . $min . '|unique:companies,sub_domain|max:50' 
                : '',
        ];

        // Password validation
        if (request()->has('password_confirmation')) {
            $rules['password'] = 'required|confirmed|min:8'; // Require confirmation if field exists
        } else {
            $rules['password'] = 'required|min:8'; // Otherwise just require minimum length
        }

        $global = global_setting();

        // Require terms and conditions if enabled globally
        if ($global && $global->sign_up_terms == 'yes') {
            $rules['terms_and_conditions'] = 'required';
        }

        // Require Google reCAPTCHA v2 if active
        if ($global->google_recaptcha_v2_status == 'active') {
            $rules['g-recaptcha-response'] = 'required';
        }

        // Optional Google reCAPTCHA v3 logic (commented)
        // if ($global->google_recaptcha_v3_status == 'active') {
        //     $rules['g_recaptcha'] = Rule::prohibitedIf(function () use ($global) {
        //         return !GlobalSetting::validateGoogleRecaptcha(request()->g_recaptcha);
        //     });
        // }

        // Check for email duplication with company email
        if (Company::where('company_email', '=', request()->email)->exists()) {
            $rules['email'] = 'required|email:rfc,strict|unique:users,email';
        }

        // Additional check if user already exists
        $user = User::where('users.email', request()->email)->first();
        if ($user) {
            $user->hasRole('employee') ? $rules['email'] = 'required|email:rfc,strict|unique:users' : '';
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
            'email.check_superadmin' => __('superadmin.emailAlreadyExist'),
            'terms_and_conditions.required' => __('superadmin.superadmin.acceptTerms') . ' ' . __('superadmin.superadmin.termsAndCondition'),
            'g-recaptcha-response.required' => __('superadmin.recaptchaInvalid'),
            'g_recaptcha.prohibited' => __('superadmin.recaptchaInvalid'),
            'sub_domain.regex' => __('superadmin.validationSubDomain'),
            'sub_domain.min' => __('validation.min.string', ['min' => 4]),
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * Adds the server domain suffix to the subdomain before validating.
     */
    public function prepareForValidation()
    {
        if (empty($this->sub_domain)) {
            return;
        }

        // Append the main domain to the subdomain
        $subdomain = trim($this->sub_domain, '.') . '.' . getDomain();
        $this->merge(['sub_domain' => $subdomain]);
        request()->merge(['sub_domain' => $subdomain]);
    }
}
