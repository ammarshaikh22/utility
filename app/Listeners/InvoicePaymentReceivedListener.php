<?php

namespace App\Listeners;

use App\Events\InvoicePaymentReceivedEvent;
use App\Notifications\InvoicePaymentReceived;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class InvoicePaymentReceivedListener
{
    /**
     * Handle the InvoicePaymentReceivedEvent.
     * This method retrieves all admin users for the company associated with the payment
     * and sends an invoice payment received notification to them when a payment event is triggered.
     *
     * @param InvoicePaymentReceivedEvent $event The event containing the payment data.
     * @return void
     */
    public function handle(InvoicePaymentReceivedEvent $event)
    {
        Notification::send(User::allAdmins($event->payment->company->id), new InvoicePaymentReceived($event->payment));
    }
}