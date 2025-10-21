<?php

namespace App\Http\Requests\ProjectTemplate;

use App\Http\Requests\CoreRequest;

class StoreProjectCategory extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authenticated users to create a project template category.
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
            // The category_name field is required — user must enter it.
            'category_name' => 'required'
        ];
    }
}
