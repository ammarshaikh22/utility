<?php

namespace App\Listeners;

use App\Events\InvoiceReminderEvent;
use App\Notifications\InvoiceReminder;
use Illuminate\Support\Facades\Notification;

class InvoiceReminderListener
{
    /**
     * Handle the InvoiceReminderEvent.
     * This method sends an invoice reminder notification to the specified users
     * when an invoice reminder event is triggered, using the InvoiceReminder notification class.
     *
     * @param InvoiceReminderEvent $event The event containing the invoice data and users to notify.
     * @return void
     */
    public function handle(InvoiceReminderEvent $event)
    {
        Notification::send($event->notifyUser, new InvoiceReminder($event->invoice));
    }
}