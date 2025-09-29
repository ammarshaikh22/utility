<?php
namespace App\Http\Requests\EmployeeDocs;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateRequest
 * Handles validation for updating an employee document.
 */
class UpdateRequest extends FormRequest
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
            // The 'name' field is always required
            'name'  => 'required',

            // The 'file' field is required only if 'file_delete' is set to 'yes'
            'file' => 'required_if:file_delete,yes'
        ];
    }
}
