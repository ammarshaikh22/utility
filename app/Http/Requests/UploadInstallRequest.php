<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadInstallRequest extends FormRequest
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
     * @return array Returns an array of validation rules for the request inputs:
     * - 'file': must be a file of type ZIP.
     */
    public function rules()
    {
        return [
            'file' => 'mimes:zip'
        ];
    }

}
