<?php

namespace App\Http\Requests\Designation;

use App\Http\Requests\CoreRequest;

class StoreRequest extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request (no restriction here)
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
            // 'name' field is required and must be unique in the 'designations' table
            // Unique check is scoped by company_id to prevent duplicates within the same company
            'name' => 'required|unique:designations,name,null,id,company_id,' . company()->id
        ];
    }

}
