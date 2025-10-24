<?php

namespace App\Http\Requests\SuperAdmin\ContactUs;

use GuzzleHttp\Client;
use App\Models\GlobalSetting;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ContactUsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $global = GlobalSetting::first();

        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
        ];

        // Google reCAPTCHA v2
        if ($global->google_recaptcha_v2_status == 'active') {
            $rules['g-recaptcha-response'] = 'required';
        }

        // Google reCAPTCHA v3
        if ($global->google_recaptcha_v3_status == 'active') {
            $rules['g_recaptcha'] = Rule::prohibitedIf(function () use ($global) {
                return !$this->validateGoogleRecaptcha($global->google_recaptcha_v3_secret_key, request()->g_recaptcha);
            });
        }

        return $rules;
    }

    /**
     * Custom messages for validation.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'g-recaptcha-response.required' => __('superadmin.recaptchaInvalid'),
            'g_recaptcha.prohibited' => __('superadmin.recaptchaInvalid'),
        ];
    }

    /**
     * Validate Google reCAPTCHA v3 response.
     *
     * @param string $secret
     * @param string $googleRecaptchaResponse
     * @return bool
     */
    public function validateGoogleRecaptcha($secret, $googleRecaptchaResponse)
    {
        $client = new Client();
        $response = $client->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'form_params' => [
                    'secret' => $secret,
                    'response' => $googleRecaptchaResponse,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ]
            ]
        );

        $body = json_decode((string)$response->getBody());

        return $body->success;
    }
}
