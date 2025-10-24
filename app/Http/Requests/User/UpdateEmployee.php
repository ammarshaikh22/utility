<?php

namespace App\Http\Requests\User;

use App\Http\Requests\CoreRequest;

class UpdateEmployee extends CoreRequest
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
            // Email is required and must be unique within the company, excluding the current employee
            'email' => 'required|unique:users,email,' . $this->route('employee').',id,company_id,' . company()->id,

            // Slack username is optional but must be unique within the company, excluding the current employee
            'slack_username' => 'nullable|unique:employee_details,slack_username,' . $this->route('employee').',id,company_id,' . company()->id,

            // Name is required
            'name' => 'required',
        ];
    }

}
