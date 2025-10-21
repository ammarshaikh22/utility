<?php

namespace App\Http\Requests\SuperAdmin\Role;

use App\Http\Requests\CoreRequest;
use Illuminate\Validation\Rule;

class StoreRole extends CoreRequest
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
     * Defines the rules for creating a new role for superadmin.
     * Ensures the role name is required and unique among superadmin roles (company_id is null).
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Role name is required and must be unique for superadmin roles (company_id is null)
            'name' => ['required', Rule::unique('roles')->whereNull('company_id')]
        ];
    }
}
