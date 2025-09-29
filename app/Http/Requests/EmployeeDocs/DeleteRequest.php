<?php
namespace App\Http\Requests\EmployeeDocs;

use Illuminate\Support\Facades\Request;

/**
 * Class DeleteRequest
 * Handles validation for deleting an employee document.
 */
class DeleteRequest extends Request
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
        // No specific validation rules for deletion
        return [
            //
        ];
    }
}
