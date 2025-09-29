<?php

namespace App\Http\Requests\Admin\Contract;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiscussionRequest extends FormRequest
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
            'comment' => [
                'required', // Comment is required
                function ($attribute, $value, $fail) {
                    // Remove any HTML formatting or extra spaces
                    $comment = trim_editor($value);

                    // Fail validation if the comment is empty after trimming
                    if ($comment == '') {
                        $fail(__('validation.required'));
                    }
                }
            ]
        ];
    }
}
