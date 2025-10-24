<?php

namespace App\Http\Requests\Payments;

use App\Models\Invoice;
use App\Http\Requests\CoreRequest;

class StorePayment extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Returns true — all authenticated users are authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for storing a payment.
     * 
     * These rules ensure that each payment submission contains
     * all required data and meets certain conditions depending
     * on invoice, gateway, and project associations.
     *
     * @return array
     */
    public function rules()
    {
        // Basic validation rules
        $rules = [
            'paid_on' => 'required', // Payment date must be provided
            'offline_methods' => 'required_if:gateway,==,Offline', // Offline method required if gateway is Offline
        ];

        /**
         * --- Invoice-specific Validation ---
         * If an invoice ID is present, it ensures that the payment
         * amount is valid and greater than zero if the invoice still has a due amount.
         */
        if (request('invoice_id') != '') {
            $invoice = Invoice::findOrFail(request('invoice_id')); // Fetch invoice record safely

            // If the invoice has no remaining due amount
            if ($invoice->amountDue() == 0) {
                // Only requires numeric value (no minimum check)
                $rules['amount'] = 'required|numeric';
            } else {
                // Requires at least 1 or more
                $rules['amount'] = 'required|numeric|min:1';
            }
        } 
        // If no invoice is linked, still require a valid payment amount
        else {
            $rules['amount'] = 'required|numeric|min:1';
        }

        /**
         * --- Transaction ID Validation ---
         * Ensures that each transaction ID is unique in the `payments` table
         * to prevent duplicate payment records.
         */
        if ($this->transaction_id) {
            $rules['transaction_id'] = 'unique:payments,transaction_id';
        }

        /**
         * --- Client Validation ---
         * If a default client is provided, the user must associate the payment
         * with either an invoice OR a project (but not both required).
         */
        if (request('default_client') != '') {
            $rules['invoice_id'] = 'required_without:project_id';
            $rules['project_id'] = 'required_without:invoice_id';
        }

        return $rules;
    }

    /**
     * Define custom attribute names for error messages.
     * 
     * These make the error messages more user-friendly by
     * replacing field names with readable labels.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'invoice_id' => __('app.invoice'),
            'project_id' => __('app.project'),
        ];
    }
}
