<?php

namespace App\Http\Requests\SuperAdmin\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request. You can add custom logic to restrict access if needed.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Define validation rules for the request input fields
        return [
            // 'email' field is required, must be a valid email format (strict RFC), and unique in the users table
            'email' => 'required|email:rfc,strict|unique:users,email',

            // 'name' field is required
            'name'  => 'required'
        ];
    }

}
