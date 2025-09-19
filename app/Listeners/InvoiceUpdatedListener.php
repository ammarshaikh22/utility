<?php

namespace App\Listeners;

use App\Events\InvoiceUpdatedEvent;
use App\Notifications\InvoiceUpdated;
use Illuminate\Support\Facades\Notification;

class InvoiceUpdatedListener
{
    /**
     * Handle the InvoiceUpdatedEvent.
     * This method sends an invoice updated notification to the specified users
     * when an invoice updated event is triggered, using the InvoiceUpdated notification class,
     * but only if the application is not running in console or seeding mode.
     *
     * @param InvoiceUpdatedEvent $event The event containing the invoice data and users to notify.
     * @return void
     */
    public function handle(InvoiceUpdatedEvent $event)
    {
        if (!isRunningInConsoleOrSeeding()) {
            Notification::send($event->notifyUser, new InvoiceUpdated($event->invoice));
        }
    }
}