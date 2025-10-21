<?php

namespace App\Http\Requests\Tickets;

use App\Http\Requests\CoreRequest;

class UpdateTicket extends CoreRequest
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
            // 'user_id' is required if the type of request is 'note'
            'user_id' => 'required_if:type,note',

            // 'message2' is required if the type of request is 'note'
            'message2' => 'required_if:type,note',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Message for missing agent field
            'user_id' => __('messages.agentFieldRequired'),

            // Message for missing description field
            'message2' => __('messages.descriptionFieldRequired'),
        ];
    }

}
