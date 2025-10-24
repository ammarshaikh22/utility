<?php

namespace App\Http\Requests\TemplateTasks;

use App\Http\Requests\CoreRequest;

class StoreTask extends CoreRequest
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
        // Define validation rules for storing a task
        return [
            'heading' => 'required', // 'heading' field must be provided
            'priority' => 'required' // 'priority' field must be provided
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        // Return custom error messages for validation
        return [
          'project_id.required' => __('messages.chooseProject'), // Message if project_id is missing
        ];
    }

}
