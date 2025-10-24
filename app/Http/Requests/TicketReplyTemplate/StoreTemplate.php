<?php

namespace App\Http\Requests\TicketReplyTemplate;

use App\Http\Requests\CoreRequest;

class StoreTemplate extends CoreRequest
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
            // 'reply_heading' is required
            'reply_heading' => 'required',

            // 'description' is required and must not be empty after trimming editor content
            'description' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (trim_editor($value) == '') {
                        // Fail validation if trimmed editor content is empty
                        $fail(__('validation.required'));
                    }
                }
            ]
        ];
    }

}
