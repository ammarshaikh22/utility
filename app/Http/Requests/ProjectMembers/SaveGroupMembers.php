<?php

namespace App\Http\Requests\ProjectMembers;

use App\Http\Requests\CoreRequest;

class SaveGroupMembers extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Returns true — meaning the user is authorized
     * to perform this request (authorization handled elsewhere).
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for saving project group members.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // At least one group ID must be provided
            'group_id.0' => 'required',

            // The project to which the groups are being assigned is required
            'project_id' => 'required',
        ];
    }

    /**
     * Custom validation messages for user-friendly errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // If no group is selected, show a localized error message
            'group_id.0.required' => __('validation.selectAtLeastOne'),
        ];
    }
}
