<?php

namespace App\Http\Requests\Login;

use Illuminate\Foundation\Http\FormRequest;
use Laravel\Fortify\Fortify;

class VerifyLoginRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * This method always returns true, allowing any user
     * to attempt a login request.
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
     * This method defines validation rules for verifying login credentials.
     * - The username field (determined by Fortify configuration) is required and must be a string.
     * - The password field is also required and must be a string.
     *
     * @return array
     */
    public function rules()
    {
        return [
            Fortify::username() => 'required|string',
            'password' => 'required|string',
        ];
    }

}
