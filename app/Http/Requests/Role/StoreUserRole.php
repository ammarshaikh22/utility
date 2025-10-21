<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\CoreRequest;

class StoreUserRole extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authorized users to assign roles to users
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
            // At least one user must be selected when assigning a role
            'user_id.0' => 'required'
        ];
    }

    /**
     * Custom error messages for validation failures.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Message shown if no user is selected
            'user_id.0.required' => 'Choose at-least 1 member'
        ];
    }
}
