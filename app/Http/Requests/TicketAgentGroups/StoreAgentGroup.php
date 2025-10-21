<?php

namespace App\Http\Requests\TicketAgentGroups;

use App\Http\Requests\CoreRequest;

class StoreAgentGroup extends CoreRequest
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
        // Define validation rules for storing an agent group
        return [
            'user_id' => 'required', // 'user_id' field must be provided
            'group_id' => 'required' // 'group_id' field must be provided
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

            'group_id.required' => __('modules.tickets.groupName').' '.__('app.required')
            // Message if group_id is missing
        ];
    }

}
