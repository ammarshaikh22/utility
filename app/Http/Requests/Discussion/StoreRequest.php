<?php

namespace App\Http\Requests\Discussion;

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
        // Allow all users to make this request (no restrictions here)
        return true;
    }

    /**
     * Modify input data before validation.
     * This trims/cleans the editor content for 'description' field.
     */
    public function prepareForValidation()
    {
        $this->merge([
            'description' => trim_editor($this->description) // Ensures clean description before validation
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Discussion category is required
            'discussion_category' => 'required',

            // Title of the discussion is required
            'title' => 'required',

            // Description (cleaned in prepareForValidation) is required
            'description' => 'required',
        ];
    }

    /**
     * Define custom attribute names for validation messages.
     * This replaces 'description' with 'Reply' in error messages.
     */
    public function attributes()
    {
        return [
            'description' => __('app.reply'),
        ];
    }

}
