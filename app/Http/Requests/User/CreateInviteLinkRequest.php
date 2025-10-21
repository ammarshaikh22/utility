<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateInviteLinkRequest extends FormRequest
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
        $rules = [
            // 'allow_email' field is required
            'allow_email' => 'required',
        ];

        // If 'allow_email' is set to 'selected', validate 'email_domain'
        if ($this->allow_email === 'selected') {
            $rules['email_domain'] = 'required|regex:/^(?!:\/\/)(?=.{1,255}$)((.{1,63}\.){1,127}(?![0-9]*$)[a-z0-9-]+\.?)$/i';
        }

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
            // Custom message for invalid email domain format
            'email_domain.regex' => __('validation.email_domain')
        ];
    }

}
