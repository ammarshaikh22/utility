<?php

namespace App\Http\Requests\ClientDocs;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateRequest
 * Handles validation when creating a new client document.
 */
class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authorized users (adjust logic if needed)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'file' => 'required',
        ];
    }
}
