<?php

namespace App\Http\Requests\SuperAdmin\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRole extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Allows all users to make this request by returning true.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Ensures the role name is required and unique for superadmin roles,
     * ignoring the current role being updated.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required', 
                // Unique among superadmin roles (company_id null), excluding current role
                Rule::unique('roles')
                    ->where('id', '<>', $this->route('role_permission'))
                    ->whereNull('company_id')
            ]
        ];
    }
}
