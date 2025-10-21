<?php

namespace App\Http\Requests\Message;

use App\Http\Requests\CoreRequest;

class ClientChatStore extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true, meaning any user can attempt
     * to send a chat message request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Defines validation rules for sending a chat message.
     * - 'message' is required to ensure a message body exists.
     * - 'user_id' is required when the sender type is 'employee'.
     * - 'admin_id' is required when the sender type is 'admin'.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => 'required',
            'user_id' => 'required_if:user_type,employee',
            'admin_id' => 'required_if:user_type,admin',
        ];
    }

    /**
     * Custom validation messages for the rules defined above.
     *
     * Provides user-friendly error messages when validation fails.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_id.required_if' => 'Select a user to send the message',
            'admin_id.required_if' => 'Select an admin to send the message',
        ];
    }

}
