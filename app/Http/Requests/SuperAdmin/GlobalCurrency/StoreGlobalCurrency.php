<?php

namespace App\Http\Requests\SuperAdmin\GlobalCurrency;

use App\Http\Requests\CoreRequest;
use Illuminate\Validation\Rule;

class StoreGlobalCurrency extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            // Currency name is required and must be unique among non-deleted currencies
            'currency_name' => [
                'required',
                Rule::unique('global_currencies')->where('deleted_at', null)->ignore($this->route('global_currency_setting')),
            ],
            // Currency symbol is required
            'currency_symbol' => 'required',
            // USD price is required if the currency is cryptocurrency
            'usd_price' => 'required_if:is_cryptocurrency,yes',
            // Exchange rate is required if the currency is not cryptocurrency
            'exchange_rate' => 'required_if:is_cryptocurrency,no',
            // Currency code is required and must be unique among non-deleted currencies
            'currency_code' => [
                'required',
                Rule::unique('global_currencies')->where('deleted_at', null)->ignore($this->route('global_currency_setting')),
            ],
        ];
    }

}
