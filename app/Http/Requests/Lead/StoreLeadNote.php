<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\CoreRequest;

class StoreLeadNote extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Returning true means any authenticated user
     * can add a lead note. You can customize this
     * later to restrict access to specific roles.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for storing a lead note.
     *
     * Validation details:
     * - `title`: required → ensures every note has a title.
     * - `details`: required → ensures the note has content.
     * - `user_id`: conditionally required → if `type` equals 1
     *   (meaning the note is assigned to an employee) and no
     *   `user_id` is provided, it becomes a required field.
     *
     * This helps differentiate between general notes and
     * employee-specific notes.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required',
            'details' => 'required',
        ];

        // Conditional rule: require 'user_id' if type == 1 and user_id is null
        if ($this->type == 1 && is_null($this->user_id)) {
            $rules['user_id'] = 'required';
        }

        return $rules;
    }

    /**
     * Custom error messages for validation failures.
     *
     * This message overrides the default message for when
     * `user_id` is missing under the conditional rule.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_id.required' => 'The employee field is required.',
        ];
    }
}
