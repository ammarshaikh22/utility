<?php

namespace App\Listeners;

use App\Events\RemovalRequestApprovedRejectLeadEvent;
use App\Notifications\RemovalRequestApprovedRejectLead;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling removal request approval/rejection events.
 *
 * - Sends a notification to the lead when their removal request
 *   is either approved or rejected.
 */
class RemovalRequestApprovedRejectLeadListener
{
    /**
     * Handle the event.
     *
     * @param RemovalRequestApprovedRejectLeadEvent $event  Event containing removal request details.
     * @return void
     */
    public function handle(RemovalRequestApprovedRejectLeadEvent $event): void
    {
        // Notify the lead with the approval/rejection status of their removal request.
        Notification::send(
            $event->removal->lead,
            new RemovalRequestApprovedRejectLead($event->removal->status)
        );
    }
}
