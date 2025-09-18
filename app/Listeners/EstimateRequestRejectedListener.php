<?php

namespace App\Listeners;

use App\Events\EstimateRequestRejectedEvent;
use App\Notifications\EstimateRequestRejected;
use Illuminate\Support\Facades\Notification;

class EstimateRequestRejectedListener
{
    /**
     * Handle the EstimateRequestRejectedEvent.
     * This method sends an estimate request rejected notification to the client associated with the estimate request
     * if the client has a valid email address, using the EstimateRequestRejected notification class.
     *
     * @param EstimateRequestRejectedEvent $event The event containing the estimate request data.
     * @return void
     */
    public function handle(EstimateRequestRejectedEvent $event): void
    {
        $company = $event->estimateRequest->company;
        $notifiable = $event->estimateRequest->client;

        if (isset($notifiable->email)) {
            Notification::send($notifiable, new EstimateRequestRejected($event->estimateRequest));
        }
    }
}