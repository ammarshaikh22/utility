<?php

namespace App\Http\Requests\Tickets;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreCustomTicket extends CoreRequest
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
        // Extend validator with a custom rule to prevent using superadmin email
        \Illuminate\Support\Facades\Validator::extend('check_superadmin', function ($attribute, $value, $parameters, $validator) {
            return !\App\Models\User::withoutGlobalScopes([\App\Scopes\ActiveScope::class, \App\Scopes\CompanyScope::class])
                ->where('email', $value)
                ->where('is_superadmin', 1)
                ->exists();
        });

        $setting = \global_setting();
        $rules = array();

        // Basic required fields
        $rules['name'] = 'required';                               // Ticket submitter name
        $rules['email'] = 'required|email:rfc,strict|check_superadmin'; // Email must be valid and not superadmin
        $rules['ticket_subject'] = 'required';                     // Subject of the ticket
        $rules['assign_group'] = 'required';                       // Assigned ticket group
        $rules['message'] = 'required|sometimes';                  // Optional message field
        $rules['ticket_description'] = 'required|sometimes';       // Optional ticket description

        // Include custom fields validation rules
        $rules = $this->customFieldRules($rules);

        // Google reCAPTCHA validation if enabled in settings
        if($setting->google_recaptcha_status == 'active' && $setting->ticket_form_google_captcha == 1 && ($setting->google_recaptcha_v2_status == 'active')){
            $rules['g-recaptcha-response'] = 'required';
        }

        return $rules;
    }

    /**
     * Define custom attributes for validation errors.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Add custom field attributes
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Message for using superadmin email
            'email.check_superadmin' => __('superadmin.emailCantUse'),
        ];
    }

}
