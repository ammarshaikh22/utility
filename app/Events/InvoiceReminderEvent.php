<?php

namespace App\Events;

// Import necessary classes
use App\Models\Invoice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invoice;     // The invoice related to the reminder
    public $reminderDate; // The date when the reminder is sent

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
