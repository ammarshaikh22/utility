<?php

namespace App\Listeners;

use App\Events\RemovalRequestAdminEvent;
use App\Notifications\RemovalRequestAdminNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling removal request admin events.
 *
 * - Notifies all system admins when a removal request is submitted.
 * - Helps ensure transparency and quick admin response to removal requests.
 */
class RemovalRequestAdminListener
{
    /**
     * Handle the event.
     *
     * @param RemovalRequestAdminEvent $event  Event containing the removal request details.
     * @return void
     */
    // phpcs:ignore
    public function handle(RemovalRequestAdminEvent $event): void
    {
        // Notify all system admins about the removal request.
        Notification::send(User::allAdmins(), new RemovalRequestAdminNotification());
    }
}
