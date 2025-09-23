<?php

namespace App\Events;

// Import necessary classes
use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewPaymentEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $payment;     // The payment instance
    public $notifyUsers; // Users to be notified about the payment

    /**
     * Create a new event instance.
     *
     * @param Payment $payment
     * @param mixed $notifyUsers
     */
    public function __construct(Payment $payment, $notifyUsers)
    {
        // Initialize properties with provided values
        $this->payment = $payment;
        $this->notifyUsers = $notifyUsers;
    }
}
