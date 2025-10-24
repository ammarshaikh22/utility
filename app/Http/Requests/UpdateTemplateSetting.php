<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateSetting extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool Returns true to allow all users to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * @return array<string, mixed> Returns an array of validation rules for the request inputs:
     * - 'template': required field.
     */
    public function rules()
    {
        return [
            'template' => 'required',
        ];
    }

}
