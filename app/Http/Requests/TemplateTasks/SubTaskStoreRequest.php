<?php
namespace App\Http\Requests\TemplateTasks;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class SubTaskStoreRequest
 * Handles validation for storing a sub-task
 * @package App\Http\Requests\TemplateTasks
 */
class SubTaskStoreRequest extends FormRequest
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
        // Define validation rules for storing a sub-task
        return [
            'title'  => 'required' // 'title' field must be provided
        ];
    }

}
