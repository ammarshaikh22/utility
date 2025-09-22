<?php

namespace App\Events;

// Import necessary classes
use App\Models\InvoiceRecurring;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewInvoiceRecurringEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invoiceRecurring;  // The new recurring invoice instance

    /**
     * Create a new event instance.
     *
     * @param InvoiceRecurring $invoiceRecurring
     */
    public function __construct(InvoiceRecurring $invoiceRecurring)
    {
        // Initialize the invoiceRecurring property with the provided value
        $this->invoiceRecurring = $invoiceRecurring;
    }
}
