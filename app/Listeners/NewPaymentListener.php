<?php

namespace App\Listeners;

use App\Events\NewPaymentEvent;
use App\Notifications\NewPayment;
use Illuminate\Support\Facades\Notification;

class NewPaymentListener
{
    /**
     * Handle the event when a new payment is recorded.
     *
     * @param NewPaymentEvent $event  The event object containing payment details and the users to notify.
     * @return void
     */
    public function handle(NewPaymentEvent $event)
    {
        // Send a "New Payment" notification to all users specified in the event.
        // The notification includes the payment details.
        Notification::send($event->notifyUsers, new NewPayment($event->payment));
    }
}
