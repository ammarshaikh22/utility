<?php

namespace App\Events;

// Import necessary classes
use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoicePaymentReceivedEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $payment;  // The payment instance that is received

    /**
     * Create a new event instance.
     *
     * @param Payment $payment
     */
    public function __construct(Payment $payment)
    {
        // Initialize the payment property with the provided value
        $this->payment = $payment;
    }
}
