<?php

namespace App\Http\Requests\EmployeeShift;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkShift extends FormRequest
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
            // Required if shift assignment is by month
            'year' => 'required_if:assign_shift_by,month',
            'month' => 'required_if:assign_shift_by,month',

            // Required if shift assignment is by specific date
            'multi_date' => 'required_if:assign_shift_by,date',

            // Ensure at least one user is selected
            'user_id.0' => 'required',
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
