<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;      // The updated Order model
    public $notifyUser; // User to notify (customer/admin)

    /**
     * @param Order $order
     * @param mixed $notifyUser
     */
    public function __construct(Order $order, $notifyUser)
    {
        $this->order = $order;
        $this->notifyUser = $notifyUser;
    }
}
