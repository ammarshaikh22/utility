<?php

namespace App\Http\Requests\Invoices;

use App\Http\Requests\CoreRequest;

class UpdateRecurringInvoice extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This method returns true, meaning all authenticated users are
     * authorized to perform this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for updating a recurring invoice.
     *
     * This method dynamically generates rules depending on conditions
     * such as invoice count, shipping address visibility, and project association.
     *
     * @return array
     */
    public function rules()
    {
        // Automatically set "show_shipping_address" to 'yes' or 'no' 
        // based on whether the request includes it.
        $this->has('show_shipping_address') 
            ? $this->request->add(['show_shipping_address' => 'yes']) 
            : $this->request->add(['show_shipping_address' => 'no']);

        // Get company settings (e.g., date format)
        $setting = company();

        // Base validation rules applied in all cases
        $rules = [
            'sub_total' => 'required',               // Subtotal amount is required
            'total' => 'required',                   // Total amount is required
            'billing_cycle' => 'integer|min:-1',     // Billing cycle must be integer and at least -1
        ];

        // Apply extra rules when this is the first invoice
        if ($this->invoice_count == 0) {
            $rules['issue_date'] = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:' . now()->format($setting->date_format);
            $rules['currency_id'] = 'required';     // Currency is required
            $rules['client_id'] = 'required';       // Client must be selected
        }

        // If user chose to show the shipping address, make it mandatory
        if ($this->show_shipping_address == 'on') {
            $rules['shipping_address'] = 'required';
        }

        // If there’s no associated project and it’s the first invoice, client_id is mandatory
        if ($this->project_id == '' && $this->invoice_count == 0) {
            $rules['client_id'] = 'required';
        }

        // Return all compiled validation rules
        return $rules;
    }
}
