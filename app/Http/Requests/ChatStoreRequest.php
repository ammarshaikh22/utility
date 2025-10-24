<?php

namespace App\Http\Requests;

class ChatStoreRequest extends CoreRequest
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
     * Prepare the data for validation.
     * Trims the message content before applying validation rules.
     */
    public function prepareForValidation()
    {
        $this->merge([
            'message' => trim_editor($this->message),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            // user_id is required if user_type is employee
            'user_id' => 'required_if:user_type,employee',

            // client_id is required if user_type is client
            'client_id' => 'required_if:user_type,client',
        ];

        // If the request type is 'modal', the message is required
        if ($this->types == 'modal') {
            $rules['message'] = 'required';
        }

        return $rules;
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom message for missing user selection
            'user_id.required_if' => 'Select a user to send the message',

            // Custom message for missing client selection
            'client_id.required_if' => 'Select a client to send the message',
        ];
    }

}
