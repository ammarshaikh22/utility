<?php

namespace App\Http\Requests\Tax;

use App\Http\Requests\CoreRequest;

class UpdateTax extends CoreRequest
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
        $rules = [];

        // If request comes via 'tax-setting', validate tax_name and rate_percent
        if($this->via && $this->via == 'tax-setting') {
            return $rules = [
                'tax_name' => 'required', // Tax name is required
                'rate_percent' => 'required|numeric', // Tax rate is required and numeric
            ];
        }
        else {
            if ($this->type == 'tax_name') {
                // When updating tax_name individually, it must be unique per company
                $rules = [
                    'value' => 'required|unique:taxes,tax_name,null,id,company_id,' . company()->id,
                ];
            }
            else {
                // For other types, value is required and must be numeric
                $rules = [
                    'value' => 'required|numeric'
                ];
            }
        }

        return $rules;
    }

}
