<?php

namespace App\Http\Requests\TimeLogs;

use Illuminate\Foundation\Http\FormRequest;

class StopTimer extends FormRequest
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
            // 'memo' field is required when stopping a timer
            'memo' => 'required'
        ];
    }

}
