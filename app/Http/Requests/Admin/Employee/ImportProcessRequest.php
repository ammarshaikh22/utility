<?php

namespace App\Http\Requests\Admin\Employee;

use Illuminate\Foundation\Http\FormRequest;

class ImportProcessRequest extends FormRequest
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
            'file' => 'required', // File to be imported is required
            'has_heading' => 'nullable|boolean', // Optional boolean indicating if the file has headings
            'columns' => ['required', 'array', 'min:1'], // Columns must be an array with at least one element
        ];
    }

    /**
     * Custom attribute names for error messages.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'columns.*' => 'column', // Each item in columns array is referred to as "column" in errors
        ];
    }

}
