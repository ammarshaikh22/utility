<?php

namespace App\Http\Requests\DiscussionCategory;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request (no restrictions applied)
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
            // The category name is required and must be unique
            // within the discussion_categories table, scoped to the same company
            'category_name' => 'required|unique:discussion_categories,name,null,id,company_id,' . company()->id,

            // The color field is required (used for category color coding)
            'color' => 'required'
        ];
    }

}
