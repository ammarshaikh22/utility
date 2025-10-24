<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\CoreRequest;

class StoreLeadAgent extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Returning true allows any authenticated or permitted user
     * to create or assign a lead agent.
     * 
     * You can later restrict this to certain roles (like admins or managers).
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules for storing a lead agent.
     *
     * The request must include:
     * - `agent_name`: required → ensures that the agent’s name
     *   is provided when creating or assigning a lead agent.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'agent_name' => 'required'
        ];
    }
}
