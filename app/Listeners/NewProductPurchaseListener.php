<?php

namespace App\Listeners;

use App\Events\NewProductPurchaseEvent;
use App\Notifications\NewProductPurchaseRequest;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class NewProductPurchaseListener
{
    /**
     * Create a new listener instance.
     *
     * Constructor is currently empty, but can be used later
     * for dependency injection or initialization logic if needed.
     */
    public function __construct()
    {
        // No setup required at the moment.
    }

    /**
     * Handle the event when a new product purchase occurs.
     *
     * @param NewProductPurchaseEvent $event  The event object containing invoice details.
     * @return void
     */
    public function handle(NewProductPurchaseEvent $event)
    {
        // Get all admins of the company related to this invoice.
        $admins = User::allAdmins($event->invoice->company->id);

        // Send a "New Product Purchase Request" notification to all those admins.
        Notification::send($admins, new NewProductPurchaseRequest($event->invoice));
    }
}
