<?php

namespace App\Http\Requests\Admin\Client;

use App\Http\Requests\CoreRequest;

class StoreClientNote extends CoreRequest
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
        $rules = [
            // 'title' is required for the note
            'title' => 'required',

            // 'details' is required for the note
            'details' => 'required',
        ];

        // If type is 1 (specific type), user_id must be provided unless the user is a client
        if ($this->type == 1 && is_null($this->user_id) && !in_array('client', user_roles())) {
            $rules['user_id'] = 'required';
        }

        return $rules;
    }

    /**
     * Custom error messages for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom message when employee (user_id) is required
            'user_id.required' => 'The employee field is required.',
        ];
    }
}
