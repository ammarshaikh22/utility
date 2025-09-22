<?php

namespace App\Events;

use App\Models\Deal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DealEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;              // Task associated with the deal
    public $notifyUser;        // User who will be notified
    public $notificationName;  // Name of the notification
    public $deal;              // The deal instance

    /**
     * Create a new event instance.
     *
     * @param Deal $deal
     * @param mixed $notifyUser
     * @param string $notificationName
     */
    public function __construct(Deal $deal, $notifyUser, $notificationName)
    {
        // Initialize the properties with the provided values
        $this->deal = $deal;
        $this->notifyUser = $notifyUser;
        $this->notificationName = $notificationName;
    }
}
