<?php

namespace App\Http\Requests\Team;

use App\Http\Requests\CoreRequest;

class StoreTeam extends CoreRequest
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
        // Define validation rules for the team form
        return [
            'team_name' => 'required' // 'team_name' field must be provided
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        // Return custom error messages for validation
        return [
            'team_name.required' => __('app.team').' '.__('app.required') // Message if team_name is missing
        ];
    }

}
