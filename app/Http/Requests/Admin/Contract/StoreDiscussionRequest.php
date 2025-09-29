<?php

namespace App\Http\Requests\Admin\Contract;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiscussionRequest extends FormRequest
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
                    // Trim the editor content to remove whitespace or HTML tags
                    $comment = trim_editor($value);

                    // Fail validation if the trimmed comment is empty
                    if ($comment == '') {
                        $fail(__('validation.required'));
                    }
                }
            ]
        ];
    }

}
