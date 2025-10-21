<?php

namespace App\Http\Requests\TicketGroups;

use App\Http\Requests\CoreRequest;

class StoreTicketGroup extends CoreRequest
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
        return [
            // Validate that 'group_name' is required and unique for the current company,
            // excluding the current ticket group if updating
            'group_name' => 'required|unique:ticket_groups,group_name,' . $this->route('ticket_group').',id,company_id,' . company()->id
        ];
    }

}
