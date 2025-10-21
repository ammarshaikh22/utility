<?php

namespace App\Http\Requests\KnowledgeBase;

use App\Http\Requests\CoreRequest;

class KnowledgeBaseCategoryStore extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Returning true means any authenticated user is allowed
     * to create a new knowledge base category.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules for storing a knowledge base category.
     *
     * Ensures that all required fields are present and properly formatted
     * before saving the data to the database.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category_name' => 'required' // Category name field must be provided
        ];
    }
}
