<?php

namespace App\Http\Requests\TicketType;

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
            // 'type' is required and must be unique for the current company,
            // excluding the current ticket type being updated
            'type' => 'required|unique:ticket_types,type,'.$this->route('ticketType').',id,company_id,' . company()->id,
        ];
    }

}
