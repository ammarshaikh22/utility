<?php

namespace App\Http\Requests\Notice;

use App\Http\Requests\CoreRequest;

class StoreNotice extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true, allowing any authenticated user to create a notice.
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
     * Validation rules:
     * - 'heading' is required for every notice.
     * - At least one employee must be selected if the notice is for employees.
     * - At least one client must be selected if the notice is for clients.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'heading' => 'required',
            'employees.0' => 'required_if:to,employee',
            'clients.0' => 'required_if:to,client',
        ];
    }

    /**
     * Custom error messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'employees.0.required_if' => __('messages.atleastOneValidation'),
            'clients.0.required_if' => __('messages.atleastOneValidation')
        ];
    }

}
