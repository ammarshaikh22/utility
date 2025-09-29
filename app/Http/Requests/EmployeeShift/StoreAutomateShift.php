<?php

namespace App\Http\Requests\EmployeeShift;

use Illuminate\Foundation\Http\FormRequest;

class StoreAutomateShift extends FormRequest
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
            // Ensure at least one user is selected in the user_id array
            'user_id.0' => 'required',

            // Rotation field must be provided
            'rotation' => 'required',
        ];
    }

    /**
     * Custom error messages for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Message displayed when no user is selected
            'user_id.0.required' => __('messages.atleastOneValidation')
        ];
    }

}
