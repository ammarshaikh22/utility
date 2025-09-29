<?php

namespace App\Http\Requests\Admin\Employee;

use App\Http\Requests\CoreRequest;

class StoreEmergencyContactRequest extends CoreRequest
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
            // Emergency contact's name is required and cannot exceed 50 characters
            'name' => 'required|max:50',

            // Mobile number is required
            'mobile' => 'required',

            // Relationship with the employee is required
            'relationship' => 'required',
        ];

        // If an email is provided, ensure it is valid
        if (request()->get('email')) {
            $rules['email'] = 'email:rfc';
        }

        return $rules;
    }

}
