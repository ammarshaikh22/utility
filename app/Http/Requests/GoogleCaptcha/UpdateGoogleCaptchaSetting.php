<?php

namespace App\Http\Requests\GoogleCaptcha;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateGoogleCaptchaSetting
 *
 * Handles the validation logic for updating Google reCAPTCHA settings.
 * Ensures proper configuration for either reCAPTCHA v2 or v3
 * depending on the version selected and the active status.
 */
class UpdateGoogleCaptchaSetting extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authorized users to update Google reCAPTCHA settings.
        // You may later restrict this to specific roles like superadmin.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        // Apply validation only if the Google reCAPTCHA feature is enabled.
        if ($this->has('google_recaptcha_status')) {
            $rules = [
                // Ensure that the reCAPTCHA version is specified when active.
                'version' => 'required_if:google_recaptcha_status,active',

                // If version v2 is selected, both site key and secret key are required.
                'google_recaptcha_v2_site_key' => 'required_if:version,v2',
                'google_recaptcha_v2_secret_key' => 'required_if:version,v2',

                // If version v3 is selected, both site key and secret key are required.
                'google_recaptcha_v3_site_key' => 'required_if:version,v3',
                'google_recaptcha_v3_secret_key' => 'required_if:version,v3',
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * These messages provide user-friendly feedback for missing reCAPTCHA keys.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom error messages for required fields under both versions
            'google_captcha2_site_key.required_if' => 'Site key is required',
            'google_captcha2_secret.required_if' => 'Secret key is required',
            'google_captcha3_site_key.required_if' => 'Site key is required',
            'google_captcha3_secret.required_if' => 'Secret key is required',
        ];
    }
}
