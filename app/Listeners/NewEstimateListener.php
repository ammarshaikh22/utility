<?php

namespace App\Listeners;

use App\Events\NewEstimateEvent;
use App\Notifications\NewEstimate;
use Illuminate\Support\Facades\Notification;

class NewEstimateListener
{
    /**
     * Handle the event when a new estimate is created.
     *
     * @param NewEstimateEvent $event  The event object that contains the estimate details.
     * @return void
     */
    public function handle(NewEstimateEvent $event)
    {
        // Send a "New Estimate" notification to the client associated with the estimate.
        // The notification includes details of the estimate passed from the event.
        Notification::send($event->estimate->client, new NewEstimate($event->estimate));
    }
}
