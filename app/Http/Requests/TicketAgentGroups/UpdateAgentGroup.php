<?php

namespace App\Http\Requests\TicketAgentGroups;

use App\Http\Requests\CoreRequest;

class UpdateAgentGroup extends CoreRequest
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
        // Define validation rules for updating an agent group
        return [
            'groupId.0' => 'required' // The first element of groupId array must be provided
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
            'user_id.required' => __('messages.atleastOneValidation').' '.__('modules.tickets.agent'),
            // Message if user_id is missing

            'groupId.0.required' => __('modules.tickets.groupName').' '.__('app.required')
            // Message if the first groupId is missing
        ];
    }

}
