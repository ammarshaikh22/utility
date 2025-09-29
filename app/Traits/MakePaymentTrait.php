<?php

namespace App\Traits;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Invoice;

/**
 * Trait MakePaymentTrait
 *
 * Provides functionality to create or update payments for invoices.
 * - Finds existing payment by transaction/event ID
 * - Updates or creates new payment record
 */
trait MakePaymentTrait
{
    /**
     * Generate a payment record for an invoice.
     *
     * - Checks if a payment already exists for the given transaction ID(s).
     * - If found, updates it. Otherwise, creates a new payment.
     * - Assigns invoice, project, and order references.
     *
     * @param string|null $gateway       Payment gateway name (e.g. "paypal", "stripe")
     * @param int|float   $amount        Amount paid
     * @param Invoice|Collection $invoice The related invoice
     * @param array|int|string $transactionId Transaction identifier(s)
     *                                        (can be a single ID or an array of IDs)
     * @param string      $status        Payment status (default: "pending")
     * @return Payment                   The created or updated payment record
     */
    public function makePayment($gateway, $amount, $invoice, $transactionId, $status = 'pending')
    {
        // Step 1: Build a query to check if payment already exists
        $payment = Payment::query();

        if (is_array($transactionId)) {
            // If multiple transaction IDs, check against both transaction_id and event_id fields
            $payment->whereIn('transaction_id', $transactionId)
                    ->orWhereIn('event_id', $transactionId);
        } else {
            // Single transaction ID
            $payment->where('transaction_id', $transactionId)
                    ->orWhere('event_id', $transactionId);
        }

        // Step 2: Fetch the most recent payment (if any)
        $payment = $payment->latest()->first();

        // Step 3: If no payment found, create a new Payment instance
        $payment = ($payment && !empty($transactionId)) ? $payment : new Payment();

        // Step 4: Fill in payment details
        $payment->project_id     = $invoice->project_id;
        $payment->invoice_id     = $invoice->id;
        $payment->order_id       = $invoice->order_id;
        $payment->gateway        = $gateway;

        // If transactionId is an array, take the first one as the identifier
        $payment->transaction_id = is_array($transactionId) ? ($transactionId[0] ?? null) : $transactionId;
        $payment->event_id       = is_array($transactionId) ? ($transactionId[0] ?? null) : $transactionId;

        $payment->currency_id    = $invoice->currency_id;
        $payment->amount         = $amount;
        $payment->paid_on        = now();  // Mark current timestamp
        $payment->status         = $status;

        // Save or update payment
        $payment->save();

        return $payment;
    }

    /**
     * Webhook endpoint placeholder.
     *
     * Prevents direct GET requests to webhook URLs and
     * enforces that only POST requests should be used.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWebhook()
    {
        return response()->json([
            'message' => 'This URL should not be accessed directly. Only POST requests are allowed.'
        ]);
    }
}
