<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRole extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authorized users to update roles
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
            // 'value' must be required and unique in the 'roles' table
            // Excludes the current role being updated using the ID from route parameter 'role_permission'
            // Ensures uniqueness is scoped to the current company
            'value' => 'required|unique:roles,name,'.$this->route('role_permission').',id,company_id,' . company()->id,
        ];
    }
}
