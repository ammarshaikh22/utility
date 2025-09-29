<?php

namespace App\Http\Requests\Designation;

use App\Http\Requests\CoreRequest;

class UpdateRequest extends CoreRequest
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
            // 'designation_name' is required and must be unique in the 'designations' table
            // It ignores the current designation ID (from route) so the existing record can be updated
            // Uniqueness is also scoped to the company_id, ensuring no duplicates within the same company
            'designation_name' => 'required|unique:designations,name,' . $this->route('designation') . ',id,company_id,' . company()->id
        ];
    }

}
