<?php

namespace App\Listeners;

use App\Events\NewCreditNoteEvent;
use App\Notifications\NewCreditNote;
use Illuminate\Support\Facades\Notification;

class NewCreditNoteListener
{
    /**
     * Create a new instance of the listener.
     *
     * This constructor is empty for now, but it's useful
     * if you want to inject dependencies or perform setup in the future.
     */
    public function __construct()
    {
        // Currently, no initialization is required.
    }

    /**
     * Handle the event when a new credit note is created.
     *
     * @param NewCreditNoteEvent $event  The event object that contains credit note details.
     * @return void
     */
    public function handle(NewCreditNoteEvent $event)
    {
        // Send a "New Credit Note" notification to the user that should be notified.
        // The notification contains the credit note details from the event.
        Notification::send($event->notifyUser, new NewCreditNote($event->creditNote));
    }
}
