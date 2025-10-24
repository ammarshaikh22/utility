<?php

namespace App\Http\Requests\Stripe;

use App\Http\Requests\CoreRequest;

/**
 * Class StoreStripeDetail
 * Handles validation for storing Stripe client details
 */
class StoreStripeDetail extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Any authenticated user can store Stripe details
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
            'clientName' => 'required', // Client's full name
            'city'       => 'required', // City of client
            'state'      => 'required', // State of client
            'country'    => 'required', // Country of client
            'line1'      => 'required', // Address line 1
        ];
    }
}
