<?php

namespace App\Http\Requests\Currency;

use App\Http\Requests\CoreRequest;

class StoreCurrencyExchangeKey extends CoreRequest
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
            // API key for the currency converter (always required)
            'currency_converter_key' => 'required',

            // Required only if the currency key version is set to "dedicated"
            'dedicated_subdomain'    => 'required_if:currency_key_version,dedicated',
        ];
    }
}
