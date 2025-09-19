<?php

namespace App\Listeners;

use App\Events\NewEstimateRequestEvent;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewEstimateRequest;

class NewEstimateRequestListener
{
    /**
     * Handle the event when a new estimate request is submitted.
     *
     * @param NewEstimateRequestEvent $event  The event object that contains the estimate request details.
     * @return void
     */
    public function handle(NewEstimateRequestEvent $event): void
    {
        // Get the company ID related to this estimate request.
        $companyId = $event->estimateRequest->company->id;

        // Notify all admins of this company with a "New Estimate Request" notification.
        // The notification includes details of the estimate request from the event.
        Notification::send(User::allAdmins($companyId), new NewEstimateRequest($event->estimateRequest));
    }
}
