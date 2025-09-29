<?php

namespace App\Http\Requests\CustomLink;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomLink extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always true here, so any authenticated user reaching
     * this request can attempt to update a custom link.
     * You could add role/permission checks if needed.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Validation rules for updating a custom link.
     */
    public function rules()
    {
        return [
            'link_title'       => 'required',   // Title must be present
            'url'              => 'required|url', // Must be a valid URL format
            'can_be_viewed_by' => 'required'    // At least one role/group must be selected
        ];
    }

    /**
     * Custom validation messages for this request.
     */
    public function messages()
    {
        return [
            'can_be_viewed_by.required' => __('messages.atleastOneRole')
            // 👆 Will display a user-friendly, translatable message instead
            // of the default validation error.
        ];
    }
}
