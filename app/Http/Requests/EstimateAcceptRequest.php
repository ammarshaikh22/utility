<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EstimateAcceptRequest extends FormRequest
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
        $rules = [
            // First name is required
            'first_name' => 'required',

            // Last name is required
            'last_name' => 'required',

            // Email is required and must be valid
            'email' => 'required|email:rfc,strict',
        ];

        // If signature type is upload, image is required
        if(request('signature_type') == 'upload'){
            $rules['image'] = 'required';
        }
        // Otherwise, the signature input is required
        else {
            $rules['signature'] = 'required';
        }

        return $rules;
    }

}
