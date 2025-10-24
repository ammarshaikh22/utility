<?php

namespace App\Http\Requests\TicketChannel;

use App\Http\Requests\CoreRequest;

class StoreTicketChannel extends CoreRequest
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
            // Validate that 'channel_name' is required and unique for the current company
            'channel_name' => 'required|unique:ticket_channels,channel_name,null,id,company_id,' . company()->id
        ];
    }

}
