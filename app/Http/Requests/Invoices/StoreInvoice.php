<?php

namespace App\Http\Requests\Invoices;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Validation\Rule;

class StoreInvoice extends CoreRequest
{
    // This trait allows custom fields to be handled dynamically in the request.
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Always authorize this request (usually controlled by middleware or policies).
        return true;
    }

    /**
     * Prepare data before validation.
     * This method formats the invoice number according to the company’s numbering format
     * before the validation rules are applied.
     */
    protected function prepareForValidation()
    {
        if ($this->invoice_number) {
            $this->merge([
                'invoice_number' => \App\Helper\NumberFormat::invoice($this->invoice_number),
            ]);
        }
    }

    /**
     * Define the validation rules for storing a new invoice.
     *
     * @return array
     */
    public function rules()
    {
        // Ensure 'show_shipping_address' has a default value of 'yes' or 'no'.
        $this->has('show_shipping_address')
            ? $this->request->add(['show_shipping_address' => 'yes'])
            : $this->request->add(['show_shipping_address' => 'no']);

        // Retrieve company settings for validation purposes (e.g., date format).
        $setting = company();

        // Core validation rules for invoice creation.
        $rules = [
            'invoice_number' => [
                'required',
                // Must be unique per company in the 'invoices' table.
                Rule::unique('invoices')->where('company_id', company()->id)
            ],
            'issue_date' => 'required',
            'sub_total' => 'required',
            'total' => 'required',
            'currency_id' => 'required',
            'exchange_rate' => 'required',
            'gateway' => 'required_if:payment_status,1', // Required if payment is marked as completed.
            'offline_methods' => 'required_if:gateway,Offline', // Required if payment gateway is "Offline".
        ];

        // Validate due date if provided, ensuring it follows the company’s date format
        // and is not earlier than the issue date.
        if ($this->has('due_date')) {
            $rules['due_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:' . $this->issue_date;
        }

        // Every invoice must be associated with a client.
        $rules['client_id'] = 'required';

        // Merge custom field rules (from trait) into main validation rules.
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Define attribute names for error messages.
     * This improves the readability of validation messages by replacing field keys
     * with human-friendly names.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Include custom field attribute names.
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    /**
     * Custom validation error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'client_id.required' => __('modules.projects.selectClient'),
            'gateway.required_if' => __('modules.projects.selectPayment')
        ];
    }
}
