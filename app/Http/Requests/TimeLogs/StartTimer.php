<?php

namespace App\Http\Requests\TimeLogs;

use App\Http\Requests\CoreRequest;

class StartTimer extends CoreRequest
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
            // 'task_id' is required if 'create_task' is not provided
            'task_id' => 'required_without:create_task',

            // 'memo' is required if 'task_id' is not provided
            'memo' => 'required_without:task_id'
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Message for missing task_id field
            'task_id.required_without' => __('messages.fieldBlank'),
        ];
    }

}
