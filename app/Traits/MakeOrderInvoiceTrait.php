<?php

namespace App\Traits;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\OrderItems;
use App\Models\InvoiceItems;
use Illuminate\Database\Eloquent\Collection;

/**
 * Trait MakeOrderInvoiceTrait
 *
 * Provides functionality to generate invoices from orders.
 * - Updates order status
 * - Links existing invoice or creates a new one
 * - Copies order items into invoice items
 */
trait MakeOrderInvoiceTrait
{

    /**
     * Generate an invoice for the given order.
     *
     * - If the order already has an invoice, update its status.
     * - Otherwise, create a new invoice and attach items.
     *
     * @param Order|Collection $order  The order instance (or a collection with one order)
     * @param string $status           Order status: "completed" (default) or "pending"/"cancelled"
     * @return Invoice                 The created or updated invoice
     */
    public function makeOrderInvoice(Order|Collection $order, $status = 'completed')
    {
        // Step 1: Update order status and save
        $order->status = $status;
        $order->save();

        // Step 2: If order already has an invoice, update its status
        if ($order->invoice) {
            /** @phpstan-ignore-next-line: Allow dynamic property access */
            $order->invoice->status = $status === 'completed' ? 'paid' : 'unpaid';
            $order->push(); // Saves order + related invoice in one go

            return $order->invoice;
        }

        // Step 3: Create a new invoice if it doesn’t exist
        $invoice = new Invoice();
        $invoice->order_id = $order->id;
        $invoice->company_id = $order->company_id;
        $invoice->client_id = $order->client_id;
        $invoice->sub_total = $order->sub_total;
        $invoice->discount = $order->discount;
        $invoice->discount_type = $order->discount_type;
        $invoice->total = $order->total;
        $invoice->currency_id = $order->currency_id;
        $invoice->status = $status === 'completed' ? 'paid' : 'unpaid';
        $invoice->note = trim_editor($order->note); // Clean up note content
        $invoice->issue_date = now();
        $invoice->send_status = 1; // Mark as ready to send
        $invoice->invoice_number = Invoice::lastInvoiceNumber() + 1; // Increment invoice sequence
        $invoice->due_amount = 0; // Mark as fully paid if completed
        $invoice->save();

        // Step 4: Copy order items into invoice items
        $orderItems = OrderItems::where('order_id', $order->id)->get();

        foreach ($orderItems as $item) {
            InvoiceItems::create([
                'invoice_id'   => $invoice->id,
                'item_name'    => $item->item_name,
                'item_summary' => $item->item_summary,
                'type'         => 'item',
                'quantity'     => $item->quantity,
                'unit_price'   => $item->unit_price,
                'amount'       => $item->amount,
                'taxes'        => $item->taxes,
                'product_id'   => $item->product_id,
                'unit_id'      => $item->unit_id,
            ]);
        }

        // Return the new invoice
        return $invoice;
    }

}
