<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\NewOrderEvent;
use App\Notifications\NewOrder;
use Illuminate\Support\Facades\Notification;

class NewOrderListener
{
    /**
     * Handle the event when a new order is created.
     *
     * @param NewOrderEvent $event  The event object containing order details and the user to notify.
     * @return void
     */
    public function handle(NewOrderEvent $event)
    {
        // Send a "New Order" notification to the user who placed the order.
        Notification::send($event->notifyUser, new NewOrder($event->order));

        // Also notify all admins of the company associated with this order.
        Notification::send(User::allAdmins($event->order->company->id), new NewOrder($event->order));
    }
}
