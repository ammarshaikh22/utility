<?php

namespace App\Http\Requests\SuperAdmin\Packages;

use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use App\Models\SuperAdmin\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
     * Defines the rules for updating a package, including conditional
     * validation based on package type, default status, and payment gateways.
     *
     * @return array
     */
    public function rules()
    {
        // Base validation rules for all packages
        $data = [
            'currency_id' => 'required|exists:global_currencies,id', // Must exist in currencies table
            'name' => [
                'required',
                // Name must be unique per currency, but ignore the current package being updated
                Rule::unique('packages')->where(function ($query) {
                    return $query->where('currency_id', $this->get('currency_id'));
                })->ignore($this->route('package')),
            ],
            'max_employees' => 'required|numeric', // Must be numeric
            'max_storage_size' => 'required|gte:-1', // Must be >= -1 (-1 may indicate unlimited)
            'storage_unit' => 'required|in:gb,mb', // Must be GB or MB
        ];

        // Fetch the current package being updated
        $package = Package::find($this->route('package'));

        // Lifetime packages (except free) must have a price
        if (request()->package == 'lifetime' && request()->package_type != 'free') {
            $data['price'] = 'required';
        }

        // If the package is a trial, add trial-specific rules
        if ($package->default === 'trial') {
            $data['no_of_days'] = 'sometimes|required|numeric|gt:0'; // Trial duration in days
            $data['trial_message'] = 'sometimes|required'; // Optional trial message

            return $data; // Return early for trial packages
        }

        // If the package is a default package, no further validation needed
        if ($package->default === 'yes') {
            return $data;
        }

        // Add description validation for non-trial and non-default packages
        $data['description'] = 'required';

        // Conditional validation for paid packages
        if (request()->package_type == 'paid') {

            // Retrieve active global payment gateways
            $gateways = GlobalPaymentGatewayCredentials::first();

            // Monthly paid package validation
            if ($this->has('monthly_status')) {
                $data['monthly_price'] = 'required|numeric|gt:0'; // Monthly price must be > 0

                // Require Razorpay monthly plan ID if Razorpay is active
                if ($gateways->razorpay_status == 'active') {
                    $data['razorpay_monthly_plan_id'] = 'required';
                }

                // Require Stripe monthly plan ID if Stripe is active
                if ($gateways->stripe_status == 'active') {
                    $data['stripe_monthly_plan_id'] = 'required';
                }
            }

            // Annual paid package validation
            if ($this->has('annual_status')) {
                $data['annual_price'] = 'required|numeric|gt:0'; // Annual price must be > 0

                // Require Razorpay annual plan ID if Razorpay is active
                if ($gateways->razorpay_status == 'active') {
                    $data['razorpay_annual_plan_id'] = 'required';
                }

                // Require Stripe annual plan ID if Stripe is active
                if ($gateways->stripe_status == 'active') {
                    $data['stripe_annual_plan_id'] = 'required';
                }
            }
        }

        // Return the complete set of validation rules
        return $data;
    }
}
