<?php

namespace App\Http\Requests\Tickets;

use App\Http\Requests\CoreRequest;

class UpdateTicketRequest extends CoreRequest
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
            // 'message' field is required
            'message' => 'required'
        ];
    }

}
