<?php

namespace App\Http\Requests\ProjectTemplate;

use App\Http\Requests\CoreRequest;

class StoreProject extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authenticated users to create a project template.
        // You can later add role-based checks if needed (e.g., only admins or managers).
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
            // The project_name field is required — the user must provide it.
            'project_name' => 'required',
        ];
    }
}
