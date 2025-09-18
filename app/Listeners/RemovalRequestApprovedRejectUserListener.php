<?php

namespace App\Listeners;

use App\Events\RemovalRequestApprovedRejectUserEvent;
use App\Notifications\RemovalRequestApprovedRejectUser;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling removal request approval/rejection events for users.
 *
 * - Notifies the user when their removal request has been either
 *   approved or rejected.
 */
class RemovalRequestApprovedRejectUserListener
{
    /**
     * Handle the event.
     *
     * @param RemovalRequestApprovedRejectUserEvent $event  Event containing removal request details.
     * @return void
     */
    public function handle(RemovalRequestApprovedRejectUserEvent $event): void
    {
        // Notify the user with the approval/rejection status of their removal request.
        Notification::send(
            $event->removal->user,
            new RemovalRequestApprovedRejectUser($event->removal->status)
        );
    }
}
