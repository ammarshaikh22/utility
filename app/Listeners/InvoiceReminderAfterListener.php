<?php

namespace App\Listeners;

use App\Events\InvoiceReminderAfterEvent;
use App\Notifications\InvoiceReminderAfter;
use Notification;

class InvoiceReminderAfterListener
{
    /**
     * Handle the InvoiceReminderAfterEvent.
     * This method sends an invoice reminder notification to the specified users
     * when an invoice reminder event is triggered after a certain condition, using the
     * InvoiceReminderAfter notification class.
     *
     * @param InvoiceReminderAfterEvent $event The event containing the invoice data and users to notify.
     * @return void
     */
    public function handle(InvoiceReminderAfterEvent $event)
    {
        Notification::send($event->notifyUser, new InvoiceReminderAfter($event->invoice));
    }
}