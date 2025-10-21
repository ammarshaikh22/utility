<?php

namespace App\Http\Requests\ProjectMembers;

use App\Http\Requests\CoreRequest;

class StoreProjectMembers extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Always return true — means all authenticated users can make this request.
        // You can modify this later to restrict certain roles (e.g., only project admins).
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
            // The first element (index 0) of the user_id array must be provided.
            // This ensures that at least one project member is selected.
            'user_id.0' => 'required',

            // The hourly rate field is optional (nullable) but must be numeric if present.
            'hourly_rate' => 'nullable|numeric',
        ];
    }

    /**
     * Custom validation messages for rule violations.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom message when no user is selected.
            // __('messages.atleastOneValidation') means it fetches this message 
            // from the language files for localization support.
            'user_id.0.required' => __('messages.atleastOneValidation'),
        ];
    }
}
