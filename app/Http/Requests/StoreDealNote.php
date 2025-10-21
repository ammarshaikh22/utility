<?php

namespace App\Http\Requests;

use App\Http\Requests\CoreRequest;

class StoreDealNote extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Allow all users to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'details' => [
                // The 'details' field is required
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
