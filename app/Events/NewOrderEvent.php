<?php

namespace App\Events;

// Import necessary classes
use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;      // The order instance
    public $notifyUser; // The user to be notified about the order

    /**
     * Create a new event instance.
     *
     * @param Order $order
     * @param mixed $notifyUser
     */
    public function __construct(Order $order, $notifyUser)
    {
        // Initialize properties with provided values
        $this->order = $order;
        $this->notifyUser = $notifyUser;
    }
}
