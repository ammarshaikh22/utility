<?php

namespace App\Http\Requests\Tasks;

use App\Http\Requests\CoreRequest;

class StoreTaskNote extends CoreRequest
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
            'note' => [
                // 'note' field is required
                'required',

                // Custom validation to ensure editor content is not empty after trimming
                function ($attribute, $value, $fail) {
                    $comment = trim_editor($value);

                    if ($comment == '') {
                        $fail(__('validation.required'));
                    }
                }
            ]
        ];
    }

}
