<?php

namespace App\Http\Requests\Admin\Employee;

use App\Http\Requests\CoreRequest;

class StorePromotionRequest extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Allow all users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Promotion date is required and must match company's date format
            'date' => 'required|date_format:"' . company()->date_format . '"',

            // Current designation ID is required and must be numeric
            'current_designation_id' => 'required|numeric',

            // Current department ID is required and must be numeric
            'current_department_id' => 'required|numeric'
        ];
    }

    /**
     * Custom error messages for validation rules
     *
     * @return array
     */
    public function messages()
    {
        return [
            'current_designation_id.required' => __('messages.SelectaDesignation'), // Error if designation is not selected
            'current_department_id.required' => __('messages.SelectaDesignation'), // Error if department is not selected
        ];
    }

}
