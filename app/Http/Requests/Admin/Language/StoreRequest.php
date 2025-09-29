<?php

namespace App\Http\Requests\Admin\Language;

use App\Http\Requests\CoreRequest;

class StoreRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authenticated users to make this request
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
            // Language name is required, unique, and max 30 characters
            'language_name' => 'required|unique:language_settings,language_name|max:30',

            // Language code is required, unique, and max 10 characters
            'language_code' => 'required|unique:language_settings,language_code|max:10',

            // Flag image or icon is required
            'flag' => 'required',

            // Status is required and max 100 characters
            'status' => 'required|max:100',

            // Right-to-left setting is required
            'is_rtl' => 'required',
        ];
    }
}
