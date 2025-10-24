<?php

namespace App\Http\Requests\User;

use App\Http\Requests\CoreRequest;

class StoreUser extends CoreRequest
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
            // Name is required
            'name' => 'required',

            // Email is required, must be valid, and unique within the company
            'email' => 'required|email:rfc,strict|unique:users,email,null,id,company_id,' . company()->id,

            // Password is required and must be at least 8 characters
            'password' => 'required|min:8',

            // Slack username is optional but must be unique within the company if provided
            'slack_username' => 'nullable|unique:employee_details,slack_username,null,id,company_id,' . company()->id
        ];
    }

}
