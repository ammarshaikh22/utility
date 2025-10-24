<?php

namespace App\Http\Requests;

class UpdateThemeSetting extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool Returns true to allow all users to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * @return array Returns an array of validation rules for the request inputs:
     * - 'primary_color.*': required and must be a valid hex color code.
     * - 'global_header_color': required and must be a valid hex color code.
     * - 'app_name': required field.
     */
    public function rules()
    {
        return [
            'primary_color.*' => [
                'required',
                'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'
            ],
            'global_header_color' => [
                'required',
                'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'
            ],
            'app_name' => 'required'
        ];
    }

    /**
     * Custom validation messages for specific rules.
     * 
     * @return array Returns an array of custom messages.
     * - 'primary_color.*.required': returns a localized message for required primary color.
     */
    public function messages()
    {
        return [
            'primary_color.*.required' => __('messages.primaryColorRequired'),
        ];
    }

}
