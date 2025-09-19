<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\OrderUpdatedEvent;
use App\Notifications\OrderUpdated;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling order update events.
 *
 * - Sends a notification to the specific user related to the order update.
 * - Also notifies all company admins about the updated order.
 */
class OrderUpdatedListener
{
    /**
     * Handle the event.
     *
     * @param OrderUpdatedEvent $event  Event containing the order and user details.
     * @return void
     */
    public function handle(OrderUpdatedEvent $event): void
    {
        // Notify the user associated with the order update.
        Notification::send($event->notifyUser, new OrderUpdated($event->order));

        // Notify all company admins about the updated order.
        Notification::send(
            User::allAdmins($event->order->company->id),
            new OrderUpdated($event->order)
        );
    }
}
