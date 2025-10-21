<?php

namespace App\Http\Requests\LeadSetting;

use App\Http\Requests\CoreRequest;

/**
 * Class StoreLeadAgent
 *
 * Handles the validation rules when assigning a lead agent
 * to one or more lead categories in the Lead Settings module.
 */
class StoreLeadAgent extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * Always returns true — meaning any authorized user can make this request.
     * You can modify this later to add permission checks (e.g., only admins).
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules for this request.
     *
     * @return array
     *
     * Validation Rules:
     * - `agent_id` → Required; ensures an agent is selected.
     * - `category_id.0` → Required; ensures at least one category
     *   is assigned to the selected agent.
     */
    public function rules()
    {
        return [
            'agent_id' => 'required',
            'category_id.0' => 'required',
        ];
    }

    /**
     * Custom validation messages for specific rules.
     *
     * @return array
     *
     * Provides a user-friendly message when no category is selected.
     */
    public function messages()
    {
        return [
            'category_id.0.required' => __('messages.atleastOneCategory'),
        ];
    }
}
