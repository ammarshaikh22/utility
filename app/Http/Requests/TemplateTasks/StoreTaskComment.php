<?php

namespace App\Http\Requests\TemplateTasks;

use App\Http\Requests\CoreRequest;

class StoreTaskComment extends CoreRequest
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
        // Define validation rules for storing a task comment
        return [
            'comment' => 'required' // 'comment' field must be provided
        ];
    }

}
