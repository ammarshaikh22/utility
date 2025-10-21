<?php

namespace App\Http\Requests\Team;

use App\Http\Requests\CoreRequest;

class UpdateDepartment extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Define validation rules for updating a department
        return [
            'team_name' => 'required|unique:teams,team_name,'.$this->route('department').',id,company_id,' . company()->id
            // 'team_name' must be provided and unique within the same company,
            // ignoring the current department being updated
        ];
    }

}
