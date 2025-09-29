<?php

namespace App\Http\Requests\Admin\SignUpSetting;

use App\Http\Requests\CoreRequest;

class SignUpSettingRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow all authenticated users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $data = [];

        // If sign-up terms are enabled, the terms link is required and must be a valid URL
        if ($this->has('sign_up_terms') && $this->sign_up_terms === 'yes') {
            $data['terms_link'] = 'required_if:sign_up_terms,yes|url';
        }

        return $data;
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'terms_link.required_if' => __('messages.signUpUrlRequired'),
        ];
    }
}
