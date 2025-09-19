<?php

namespace App\Listeners;

use App\Events\RemovalRequestAdminLeadEvent;
use App\Notifications\RemovalRequestAdminNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling removal request admin lead events.
 *
 * - Sends a notification to all company admins when a removal request is created for a lead.
 * - Ensures that admins are promptly informed about such requests.
 */
class RemovalRequestAdminLeadListener
{
    /**
     * Handle the event.
     *
     * @param RemovalRequestAdminLeadEvent $event  Event containing the removal request lead details.
     * @return void
     */
    // phpcs:ignore
    public function handle(RemovalRequestAdminLeadEvent $event): void
    {
        // Notify all admins of the company about the removal request for a lead.
        Notification::send(
            User::allAdmins($event->removalRequestLead->company->id),
            new RemovalRequestAdminNotification()
        );
    }
}
