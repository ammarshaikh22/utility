<?php

namespace App\Http\Requests\SuperAdmin\Role;

use App\Http\Requests\CoreRequest;

class StoreUserRole extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Allows all users to make this request by returning true.
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
     * Ensures at least one user is selected when assigning roles.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // The first element of user_id array must be present
            'user_id.0' => 'required'
        ];
    }

    /**
     * Custom messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Message displayed if no user is selected
            'user_id.0.required' => 'Choose at-least 1 member'
        ];
    }
}
