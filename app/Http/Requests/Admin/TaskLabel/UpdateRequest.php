<?php

namespace App\Http\Requests\Admin\TaskLabel;

use App\Http\Requests\CoreRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends CoreRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'label_name' => [
                'required',
                Rule::unique('task_label_list')->where(function ($query) {
                    $query->where('id', '<>', $this->route('task_label'));
                }),
            ],
            'color' => 'required|max:50',
        ];
    }
}
