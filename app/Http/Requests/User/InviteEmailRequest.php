<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class InviteEmailRequest extends FormRequest
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
        return [
            // 'email' field is required and must have at least one entry
            'email' => 'required|min:1',

            // Each email in the array must be required and a valid email
            'email.*' => 'required|email:rfc,strict',
        ];
    }

}
