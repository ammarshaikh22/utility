<?php

namespace App\Http\Requests\SuperAdmin\TicketType;

use App\Http\Requests\CoreRequest;

class UpdateTicketType extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request. Add custom logic if access should be restricted.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Define validation rules for updating a ticket type
        return [
            // 'type' field is required and must be unique in 'support_ticket_types' table,
            // ignoring the current record being updated (identified by route parameter)
            'type' => 'required|unique:support_ticket_types,type,'.$this->route('superadmin.support-ticketTypes'),
        ];
    }

}
