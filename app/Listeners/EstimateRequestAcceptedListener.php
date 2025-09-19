<?php

namespace App\Listeners;

use App\Events\EstimateRequestAcceptedEvent;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EstimateRequestAccepted;

class EstimateRequestAcceptedListener
{
    /**
     * Handle the EstimateRequestAcceptedEvent.
     * This method sends an estimate request accepted notification to the client associated with the estimate request
     * if the client has a valid email address, using the EstimateRequestAccepted notification class.
     *
     * @param EstimateRequestAcceptedEvent $event The event containing the estimate request data.
     * @return void
     */
    public function handle(EstimateRequestAcceptedEvent $event): void
    {
        $notifiable = $event->estimateRequest->client;

        if (isset($notifiable->email)) {
            Notification::send($notifiable, new EstimateRequestAccepted($event->estimateRequest));
        }
    }
}