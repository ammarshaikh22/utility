<?php

namespace App\Listeners;

use App\Events\PaymentReminderEvent;
use App\Notifications\PaymentReminder;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling payment reminder events.
 *
 * - Sends a reminder notification to the specified user.
 * - Ensures the user is reminded about their pending invoice.
 */
class PaymentReminderListener
{
    /**
     * Handle the event.
     *
     * @param PaymentReminderEvent $event  Event containing the user and invoice details.
     * @return void
     */
    public function handle(PaymentReminderEvent $event): void
    {
        // Notify the user with a payment reminder for the given invoice.
        Notification::send($event->notifyUser, new PaymentReminder($event->invoice));
    }
}
