<?php

namespace App\Http\Requests\Invoices;

use App\Http\Requests\CoreRequest;

class StoreRecurringInvoice extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Always authorize the request (authorization handled elsewhere, like middleware or policies)
        return true;
    }

    /**
     * Define validation rules for storing a recurring invoice.
     *
     * @return array
     */
    public function rules()
    {
        // Ensure "show_shipping_address" field always has a default value ("yes" or "no")
        $this->has('show_shipping_address')
            ? $this->request->add(['show_shipping_address' => 'yes'])
            : $this->request->add(['show_shipping_address' => 'no']);

        // Retrieve the current company's settings (for example, date format)
        $setting = company();

        // Basic validation rules required for every recurring invoice
        $rules = [
            'sub_total' => 'required',             // The subtotal amount must be provided
            'total' => 'required',                 // The total amount must be provided
            'currency_id' => 'required',           // Currency ID is required to identify currency type
            'billing_cycle' => 'required|integer|min:-1' // Billing cycle must be a valid integer (-1 may represent no end)
        ];

        /**
         * If 'immediate_invoice' is not selected:
         * - The 'issue_date' must be a valid date in the company’s date format.
         * - The date must be after the current date.
         */
        if (!$this->has('immediate_invoice')) {
            $rules['issue_date'] = 'required|date_format:"' . $setting->date_format . '"|after:' . now()->format($setting->date_format);
        }

        /**
         * If the user has enabled shipping address visibility ('on'),
         * ensure a shipping address is actually provided.
         */
        if ($this->show_shipping_address == 'on') {
            $rules['shipping_address'] = 'required';
        }

        /**
         * If this recurring invoice is not linked to any project,
         * a client must be explicitly selected.
         */
        if ($this->project_id == '') {
            $rules['client_id'] = 'required';
        }

        return $rules;
    }
}
