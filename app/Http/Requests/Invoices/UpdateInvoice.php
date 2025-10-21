<?php

namespace App\Http\Requests\Invoices;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdateInvoice extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow all authorized users to update invoices.
        // Access control (permissions/roles) can be handled in middleware or policies.
        return true;
    }

    /**
     * Define validation rules for updating an existing invoice.
     *
     * @return array
     */
    public function rules()
    {
        // Ensure "show_shipping_address" always has a consistent value ("yes" or "no")
        $this->has('show_shipping_address')
            ? $this->request->add(['show_shipping_address' => 'yes'])
            : $this->request->add(['show_shipping_address' => 'no']);

        // Retrieve current company settings (for date format, etc.)
        $setting = company();

        // Core validation rules for updating an invoice
        $rules = [
            /**
             * Invoice number:
             * - Must be provided
             * - Must be unique across the "invoices" table for the same company
             * - Exception: the current invoice being updated (so it doesn’t conflict with itself)
             */
            'invoice_number' => 'required|unique:invoices,invoice_number,' . $this->route('invoice') . ',id,company_id,' . company()->id,

            'issue_date' => 'required',            // Date the invoice was issued (required)
            'sub_total' => 'required',             // Subtotal amount before taxes/discounts
            'total' => 'required',                 // Final total amount
            'currency_id' => 'required',           // Must specify the currency being used

            /**
             * Payment-related validation:
             * - If payment status is "1" (paid), a gateway must be selected.
             * - If gateway is "Offline", an offline payment method must also be specified.
             */
            'gateway' => 'required_if:payment_status,1',
            'offline_methods' => 'required_if:gateway,Offline,payment_status,1',
        ];

        /**
         * If the request includes a "due_date" field:
         * - It must match the company’s date format.
         * - It must not be earlier than the issue date.
         */
        if ($this->has('due_date')) {
            $rules['due_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:' . $this->issue_date;
        }

        /**
         * If this invoice is not linked to a project,
         * a client must be explicitly assigned.
         */
        if ($this->project_id == '') {
            $rules['client_id'] = 'required';
        }

        /**
         * Merge any custom field validation rules defined using the CustomFieldsRequestTrait.
         */
        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    /**
     * Define human-readable names for custom fields (for error messages).
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        // Merge attributes for custom fields
        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    /**
     * Define custom error messages for specific validation failures.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'client_id.required' => __('modules.projects.selectClient'), // Message if client is not selected
            'gateway.required_if' => __('modules.projects.selectPayment') // Message if payment gateway not selected
        ];
    }
}
