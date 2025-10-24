<?php

namespace App\Http\Requests\SuperAdmin\StripePayment;

use Illuminate\Foundation\Http\FormRequest;

class StripeValidateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Allows all users to make this request by returning true.
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
     * Ensures all required billing address fields and client name are provided
     * for Stripe payment processing.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Client name is required
            'clientName' => 'required',
            // Street address line 1 is required
            'line1' => 'required',
            // City is required
            'city' => 'required',
            // State is required
            'state' => 'required',
            // Country is required
            'country' => 'required',
        ];
    }
}
