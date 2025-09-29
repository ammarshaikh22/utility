<?php

namespace App\Http\Requests\Currency;

use App\Http\Requests\CoreRequest;

class StoreCurrency extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users (can later be restricted by roles/permissions if required)
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
            // Basic currency details
            'currency_name'   => 'required',  // e.g., US Dollar
            'currency_symbol' => 'required',  // e.g., $
            'no_of_decimal'   => 'required',  // number of decimal places

            // Conditional validation
            'usd_price'    => 'required_if:is_cryptocurrency,yes', // required for crypto
            'exchange_rate'=> 'required_if:is_cryptocurrency,no',  // required for non-crypto

            // Unique validation considering company_id
            'currency_code' => 'required|unique:currencies,currency_code,null,id,company_id,' . company()->id,
        ];
    }
}
