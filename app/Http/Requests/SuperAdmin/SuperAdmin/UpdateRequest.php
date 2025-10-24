<?php

namespace App\Http\Requests\SuperAdmin\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request. Add custom logic if access needs to be restricted.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Define validation rules for updating a SuperAdmin
        return [
            // 'email' field is required and must be unique in the users table,
            // excluding the current SuperAdmin being updated (identified by route parameter 'superadmin')
            'email' => 'required|unique:users,email,'.$this->route('superadmin'),

            // 'name' field is required
            'name'  => 'required',
        ];
    }

}
