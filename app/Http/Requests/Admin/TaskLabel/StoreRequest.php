<?php

namespace App\Http\Requests\Admin\TaskLabel;

use App\Http\Requests\CoreRequest;

class StoreRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'label_name' => 'required|max:50',
            'color'      => 'required|max:50',
        ];
    }
}
