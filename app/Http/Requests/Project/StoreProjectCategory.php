<?php

namespace App\Http\Requests\Project;

use App\Http\Requests\CoreRequest;

class StoreProjectCategory extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * 
     * Always returns true — allows any authorized user to add a project category.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules for creating a new project category.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // The category name is required and must be unique per company.
            'category_name' => 'required|unique:project_category,category_name,null,id,company_id,' . company()->id
        ];
    }
}
