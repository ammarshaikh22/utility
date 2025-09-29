<?php

namespace App\Http\Requests\Admin\Language;

use App\Http\Requests\CoreRequest;

class AutoTranslateRequest extends CoreRequest
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
            // The Google API key is required for auto-translation
            'google_key' => 'required',
        ];
    }
}
