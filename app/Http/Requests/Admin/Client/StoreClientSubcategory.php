<?php

namespace App\Http\Requests\Admin\Client;

use App\Http\Requests\CoreRequest;

class StoreClientSubcategory extends CoreRequest
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
            // The parent category ID is required
            'category_id' => 'required',

            // The subcategory name is required
            'category_name' => 'required',
        ];
    }

}
