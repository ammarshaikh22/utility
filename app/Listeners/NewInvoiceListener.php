<?php

namespace App\Listeners;

use App\Events\NewInvoiceEvent;
use App\Notifications\NewInvoice;
use Illuminate\Support\Facades\Notification;

class NewInvoiceListener
{
    /**
     * Handle the event when a new invoice is created.
     *
     * This listener checks if the user has an email before sending a notification.
     * This prevents unnecessary errors or attempts to notify users without an email address.
     *
     * @param NewInvoiceEvent $event  The event object containing invoice and user details.
     * @return void
     */
    public function handle(NewInvoiceEvent $event)
    {
        // Only proceed if the user has a valid email address.
        if ($event->notifyUser->email != null) {
            // Send a "New Invoice" notification to the specified user,
            // including the invoice details from the event.
            Notification::send($event->notifyUser, new NewInvoice($event->invoice));
        }
    }
}
