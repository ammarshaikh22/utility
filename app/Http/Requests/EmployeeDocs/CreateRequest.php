<?php
namespace App\Http\Requests\EmployeeDocs;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateRequest
 * Handles validation when creating a new employee document.
 * @package App\Http\Requests\Admin\Employee
 */
class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request (no restriction applied here)
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
            // 'name' of the document is required
            'name'  => 'required',
            
            // The actual file is required for upload
            'file'  => 'required',
        ];
    }

}
