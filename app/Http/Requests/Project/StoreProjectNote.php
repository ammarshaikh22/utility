<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\CoreRequest;

class StoreProjectNote extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * 
     * Always returns true — allows any authorized user to create a project note.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for storing a project note.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            // The title of the note is required.
            'title' => 'required',
            // The content/details of the note are also required.
            'details' => 'required',
        ];

        // If the note type equals 1 and no user is assigned, require a user ID.
        if ($this->type == '1' && is_null($this->user_id)) {
            $rules['user_id'] = 'required';
        }

        return $rules;
    }

    /**
     * Custom validation error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom error message when user_id is missing.
            'user_id.required' => 'The employee field is required.',
        ];
    }
}
