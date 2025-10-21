<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractTemplate extends FormRequest
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
            // The subject of the contract is required
            'subject' => 'required',
            // The type of contract is required
            'contract_type' => 'required',
            // The amount associated with the contract is required
            'amount' => 'required'
        ];
    }

}
