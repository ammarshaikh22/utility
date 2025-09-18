<?php

namespace App\Listeners;

use App\Events\RemovalRequestApproveRejectEvent;
use App\Notifications\RemovalRequestApprovedReject;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling removal request approval/rejection events.
 *
 * - Sends a notification to the user when their removal request
 *   is either approved or rejected.
 */
class RemovalRequestApprovedRejectListener
{
    /**
     * Handle the event.
     *
     * @param RemovalRequestApproveRejectEvent $event  Event containing removal request details.
     * @return void
     */
    public function handle(RemovalRequestApproveRejectEvent $event): void
    {
        // Notify the user with the approval/rejection status of their removal request.
        Notification::send(
            $event->removalRequest->user,
            new RemovalRequestApprovedReject($event->removalRequest->status)
        );
    }
}
