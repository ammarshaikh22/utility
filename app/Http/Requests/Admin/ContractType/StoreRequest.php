<?php

namespace App\Http\Requests\Admin\ContractType;

use App\Http\Requests\CoreRequest;

class StoreRequest extends CoreRequest
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
        // Validation rules for creating a new contract type
        return [
            'name' => 'required|' . // Name is required
                      'unique:contract_types,name,null,id,company_id,' . company()->id . '|' . // Must be unique for this company
                      'max:100' // Maximum length of 100 characters
        ];
    }

}
