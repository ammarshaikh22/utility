<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\CoreRequest;

class StoreRole extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authorized users to create a new role
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
            // The 'name' field is required and must be unique per company
            'name' => 'required|unique:roles,name,null,id,company_id,' . company()->id
        ];
    }
}
