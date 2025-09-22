<?php

namespace App\Events;

// Import necessary classes
use App\Models\Invoice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderAfterEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invoice;     // The invoice for which the reminder is sent
    public $reminderDate; // The date when the reminder is triggered

    /**
     * Create a new event instance.
     *
     * @param Invoice $invoice
     * @param string $reminderDate
     */
    public function __construct(Invoice $invoice, $reminderDate)
    {
        // Initialize the properties with the provided values
        $this->invoice = $invoice;
        $this->reminderDate = $reminderDate;
    }
}
