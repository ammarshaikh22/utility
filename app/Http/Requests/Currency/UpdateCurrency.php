<?php

namespace App\Http\Requests\Currency;

use App\Http\Requests\CoreRequest;

class UpdateCurrency extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all users (authorization can be restricted later if needed)
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
            // Name of the currency (required)
            'currency_name'   => 'required',

            // Symbol of the currency (required)
            'currency_symbol' => 'required',

            // USD price is mandatory if the currency is a cryptocurrency
            'usd_price'       => 'required_if:is_cryptocurrency,yes',

            // Exchange rate is mandatory if the currency is not a cryptocurrency
            'exchange_rate'   => 'required_if:is_cryptocurrency,no',

            // Currency code must be unique within the company,
            // excluding the current record being updated
            'currency_code'   => 'required|unique:currencies,currency_code,' 
                                . $this->route('currency_setting') 
                                . ',id,company_id,' . company()->id,
        ];
    }
}
