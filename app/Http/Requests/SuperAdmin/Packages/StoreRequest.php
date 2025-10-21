<?php

namespace App\Http\Requests\SuperAdmin\Packages;

use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This function is used to check if the current user has permission
     * to make this request. Returning `true` allows everyone to make it.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Authorization is allowed for all users
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * This function defines the validation rules for storing a package.
     * These rules ensure that required fields are present, unique, numeric,
     * or match certain conditions based on the package type.
     *
     * @return array
     */
    public function rules()
    {
        // Base validation rules that apply to all packages
        $data = [
            'currency_id' => 'required|exists:global_currencies,id', // Must exist in currencies table
            'name' => [
                'required',
                // Ensure name is unique for a given currency
                Rule::unique('packages')->where(function ($query) {
                    return $query->where('currency_id', $this->get('currency_id'));
                }),
            ],
            'description' => 'required', // Package must have a description
            'max_employees' => 'required|numeric', // Must be a numeric value
            'max_storage_size' => 'required|gte:-1', // Must be >= -1 (-1 could indicate unlimited)
            'storage_unit' => 'required|in:gb,mb', // Must be either GB or MB
        ];

        // Retrieve first record of global payment gateways to check which gateways are active
        $gateways = GlobalPaymentGatewayCredentials::first();

        // Conditional validation: Lifetime packages (except free) require a price
        if (request()->package == 'lifetime' && request()->package_type != 'free') {
            $data['price'] = 'required';
        }

        // Conditional validation for monthly paid packages
        if (request()->package_type == 'paid' && $this->has('monthly_status') && request()->package != 'lifetime') {
            $data['monthly_price'] = 'required|numeric|gt:0'; // Monthly price must be > 0

            // If both annual and monthly prices exist and Razorpay is active, require plan IDs
            if (($this->get('annual_price') > 0 && $this->get('monthly_price') > 0) && $gateways->razorpay_status == 'active') {
                $data['razorpay_annual_plan_id'] = 'required';
                $data['razorpay_monthly_plan_id'] = 'required';
            }
        }

        // Conditional validation for annual paid packages
        if (request()->package_type == 'paid' && $this->has('annual_status') && request()->package != 'lifetime') {
            $data['annual_price'] = 'required|numeric|gt:0'; // Annual price must be > 0

            // If both prices exist and Stripe is active, require plan IDs
            if ($this->get('annual_price') > 0 && $this->get('monthly_price') > 0 && $gateways->stripe_status == 'active') {
                $data['stripe_annual_plan_id'] = 'required';
                $data['stripe_monthly_plan_id'] = 'required';
            }
        }

        // Return the full set of validation rules
        return $data;
    }
}
