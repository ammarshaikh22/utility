<?php

namespace App\Http\Requests\Admin\App;

use App\Http\Requests\CoreRequest;

class UpdateAppSetting extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        // Validate allowed file types (optional, but if present, required)
        $rules['allowed_file_types'] = 'sometimes|required';

        // Validate allowed file size (optional, numeric, min 4, max 900000)
        $rules['allowed_file_size'] = 'sometimes|required|numeric|min:4|max:900000';

        return $rules;
    }
}
