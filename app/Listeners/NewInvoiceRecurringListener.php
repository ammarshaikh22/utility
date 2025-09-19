<?php

namespace App\Listeners;

use App\Events\NewInvoiceRecurringEvent;
use App\Notifications\InvoiceRecurringStatus;
use App\Notifications\NewRecurringInvoice;
use App\Models\User;
use App\Scopes\ActiveScope;
use Illuminate\Support\Facades\Notification;

class NewInvoiceRecurringListener
{
    /**
     * Create a new listener instance.
     *
     * Currently empty, but can be used later for dependency injection
     * or initialization logic if needed.
     */
    public function __construct()
    {
        // No setup required at the moment.
    }

    /**
     * Handle the event when a recurring invoice is processed.
     *
     * Steps performed:
     *  1. Check if the request type is "send" → only then proceed with notifications.
     *  2. Determine the client ID either from the related project or directly from the invoice.
     *  3. Retrieve the client user (ignoring the ActiveScope so inactive clients can still be notified).
     *  4. Send the correct notification based on the event status:
     *     - "status" → notify client about recurring invoice status update.
     *     - otherwise → notify client about a new recurring invoice.
     *
     * @param NewInvoiceRecurringEvent $event  The event containing invoice details and status.
     * @return void
     */
    public function handle(NewInvoiceRecurringEvent $event)
    {
        // Only proceed if the request type is set to "send"
        if (request()->type && request()->type == 'send') {
            
            // Ensure there is a valid client ID (either from project or directly from invoice)
            if (($event->invoice->project && $event->invoice->project->client_id != null) || $event->invoice->client_id != null) {
                
                // Pick client ID from project if available, otherwise from invoice directly
                $clientId = ($event->invoice->project && $event->invoice->project->client_id != null) 
                    ? $event->invoice->project->client_id 
                    : $event->invoice->client_id;

                // Retrieve client user, bypassing the ActiveScope (so inactive clients can still receive notifications)
                $notifyUser = User::withoutGlobalScope(ActiveScope::class)->findOrFail($clientId);

                // Case 1: Status update for recurring invoice
                if ($event->status == 'status') {
                    Notification::send($notifyUser, new InvoiceRecurringStatus($event->invoice));
                }
                // Case 2: New recurring invoice created
                else {
                    Notification::send($notifyUser, new NewRecurringInvoice($event->invoice));
                }
            }
        }
    }
}
