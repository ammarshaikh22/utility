<?php

namespace App\Http\Requests\Admin\ContractType;

use App\Http\Requests\CoreRequest;

class UpdateRequest extends CoreRequest
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
        // Validation rules for updating an existing contract type
        return [
            'name' => 'required|' . // Name is required
                      'max:100|' . // Maximum length of 100 characters
                      'unique:contract_types,name,' . $this->route('type') . ',id,company_id,' . company()->id
                      // Must be unique for this company, ignoring the current contract type being updated
        ];
    }

}
