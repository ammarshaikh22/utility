<?php

namespace App\Http\Requests\Admin\Language;

use App\Http\Requests\CoreRequest;

class UpdateRequest extends CoreRequest
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
            // Language name is required, max 30 characters, and unique except for the current ID
            'language_name' => 'required|max:30|unique:language_settings,language_name,' . $this->route('id') . ',id',

            // Language code is required, only alpha-numeric/dash/underscore, max 10 chars, unique except current
            'language_code' => 'required|alpha_dash|max:10|unique:language_settings,language_code,' . $this->route('id') . ',id',

            // Status is required
            'status' => 'required',

            // Flag image or icon is required
            'flag' => 'required',

            // Right-to-left setting is required
            'is_rtl' => 'required',
        ];
    }
}
