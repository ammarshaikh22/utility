<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkPayments extends FormRequest
{
    /**
     * Authorize the user to make this request.
     * Returns true — any authenticated user can perform this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define validation rules for bulk payment submission.
     * Applies different rules dynamically for each invoice in the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [];

        // Retrieve all invoice numbers from the request
        $invoiceIds = request()->invoice_number;

        // Loop through each invoice to apply individual validation
        foreach ($invoiceIds as $index => $invoiceId) {

            // Extract related fields for each invoice
            $amount = request()->amount[$index];
            $transaction_id = request()->transaction_id[$index];
            $gateway = request()->gateway[$index];
            $offline_method_id = request()->offline_method_id[$index];
            $payment_date = request()->payment_date[$index];

            // If the gateway is "Offline" but no offline method is selected
            if ($gateway != 'all' && $gateway == 'Offline' && $offline_method_id == null) {
                $rules['payment_date.' . $index] = 'required'; // Date required
                $rules['offline_methods.' . $index] = 'required'; // Offline method required
                $rules['amount.' . $index] = 'required'; // Amount required
            }
            // If payment is online (not "Offline" or "all")
            elseif ($gateway != 'all' && $gateway != 'Offline') {
                $rules['payment_date.' . $index] = 'required';
                $rules['amount.' . $index] = 'required';
            }

            // If payment is Offline, ensure amount is required
            if ($gateway != 'all' && $gateway == 'Offline') {
                $rules['amount.' . $index] = 'required';
            }

            // If gateway is "all" but an amount is entered, force selecting a gateway
            if ($gateway == 'all' && (!is_null($amount) || $amount != null)) {
                $rules['gateway.' . $index] = 'required';
            }

            // Payment date is mandatory for all valid cases
            if (is_null($payment_date) || $payment_date == null) {
                $rules['payment_date.' . $index] = 'required';
            }
        }

        return $rules;
    }

    /**
     * Custom error messages for each validation rule.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        $message = [];

        $invoiceIds = request()->invoice_number;

        // Generate dynamic error messages per invoice index
        foreach ($invoiceIds as $index => $invoiceId) {

            $amount = request()->amount[$index];
            $transaction_id = request()->transaction_id[$index];
            $gateway = request()->gateway[$index];
            $offline_method_id = request()->offline_method_id[$index];
            $payment_date = request()->payment_date[$index];

            // Offline gateway with missing offline method
            if ($gateway != 'all' && $gateway == 'Offline' && $offline_method_id == null) {
                $message['payment_date.' . $index] = __('messages.invoiceDateError');
                $message['offline_methods.' . $index] = __('messages.selectOfflineMethod');
                $message['amount.' . $index] = __('messages.invoicePaymentError');
            }
            // Online payment gateway
            elseif ($gateway != 'all' && $gateway != 'Offline') {
                $message['payment_date.' . $index] = __('messages.invoiceDateError');
                $message['amount.' . $index] = __('messages.invoicePaymentError');
            }

            // Offline payment still requires amount
            if ($gateway != 'all' && $gateway == 'Offline') {
                $message['amount.' . $index] = __('messages.invoicePaymentError');
            }

            // Gateway “all” with entered amount but no gateway selected
            if ($gateway == 'all' && (!is_null($amount) || $amount != null)) {
                $message['gateway.' . $index] = __('messages.selectGateway');
            }

            // Missing payment date
            if (is_null($payment_date) || $payment_date == null) {
                $message['payment_date.' . $index] = __('messages.invoiceDateError');
            }
        }

        return $message;
    }
}
