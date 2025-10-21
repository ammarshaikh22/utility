<?php

namespace App\Http\Requests\TicketReplyTemplate;

use App\Http\Requests\CoreRequest;

class UpdateTemplate extends CoreRequest
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
            // 'reply_heading' is required
            'reply_heading' => 'required',

            // 'description' is required
            'description' => 'required'
        ];
    }

}
