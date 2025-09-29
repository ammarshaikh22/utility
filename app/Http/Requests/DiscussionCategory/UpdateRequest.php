<?php

namespace App\Http\Requests\DiscussionCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users to make this request (no restriction applied here)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Apply rules only if the request contains a 'name' field
        if (request()->has('name')) {
            return [
                // 'name' is required and must be unique in the discussion_categories table
                // Excludes the current record (using route parameter 'discussion_category')
                // Ensures uniqueness is also scoped to the current company
                'name' => 'required|unique:discussion_categories,name,' 
                    . $this->route('discussion_category') . ',id,company_id,' . company()->id,
            ];
        }

        // If no 'name' is provided, return an empty rule set
        return [];
    }

}
