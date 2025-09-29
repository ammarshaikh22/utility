<?php

namespace App\Http\Requests\CustomLink;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomLink extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always true here, so anyone reaching this request
     * can attempt to store a custom link.
     * You could add logic to check roles/permissions if needed.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Validation rules for storing a custom link.
     */
    public function rules()
    {
        return [
            'link_title'       => 'required',   // Title of the custom link must be provided
            'url'              => 'required|url', // Must be present and a valid URL format
            'can_be_viewed_by' => 'required'    // Must specify at least one role/user group
        ];
    }

    /**
     * Custom validation messages.
     * Here it overrides the default message for `can_be_viewed_by.required`.
     */
    public function messages()
    {
        return [
            'can_be_viewed_by.required' => __('messages.atleastOneRole')
            // 👆 This uses Laravel's localization system.
            // "messages.atleastOneRole" should be defined in your language files.
        ];
    }
}
