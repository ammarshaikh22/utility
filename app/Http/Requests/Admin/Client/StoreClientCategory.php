<?php

namespace App\Http\Requests\Admin\Client;

use App\Http\Requests\CoreRequest;

class StoreClientCategory extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request
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
            // Validate that category_name is required and has a maximum length of 100 characters
            'category_name' => 'required|max:100'
        ];
    }
}
