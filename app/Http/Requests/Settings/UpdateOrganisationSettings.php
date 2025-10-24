<?php

namespace App\Http\Requests\Settings;

use App\Http\Requests\CoreRequest;

class UpdateOrganisationSettings extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // All authorized users can update organization settings
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            // Company name is required, max length 60
            'company_name' => 'required|max:60',

            // Company email is required, must be a valid email, max length 100
            'company_email' => 'required|email:rfc,strict|max:100',

            // Company phone is required, max length 20
            'company_phone' => 'required|max:20',

            // Website is optional, must be a valid URL if provided, max length 50
            'website' => 'nullable|url|max:50'
        ];

        // If Google reCAPTCHA is enabled, validate its key and secret
        if ($this->has('google_recaptcha') && $this->google_recaptcha == 'on') {
            $rules['google_recaptcha_key'] = 'required';
            $rules['google_recaptcha_secret'] = 'required';
        }

        return $rules;
    }
}
