<?php

namespace App\Http\Requests\KnowledgeBase;

use App\Http\Requests\CoreRequest;

class KnowledgeBaseStore extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Returning true means that any authorized user can
     * create a new knowledge base article.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define the validation rules for storing a knowledge base article.
     *
     * These rules ensure that required fields are present
     * and properly validated before saving the record.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'heading' => 'required', // The article title must be provided
            'category' => 'required', // Each article must belong to a category
        ];
    }
}
