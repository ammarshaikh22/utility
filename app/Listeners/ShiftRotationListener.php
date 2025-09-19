<?php

namespace App\Listeners;

use App\Events\ShiftRotationEvent;
use App\Notifications\ShiftRotationNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Listener for handling shift rotation events.
 *
 * - Notifies employees about their new shift rotation schedule.
 */
class ShiftRotationListener
{
    /**
     * Handle the event.
     *
     * @param ShiftRotationEvent $event  Event containing employee and shift details.
     * @return void
     */
    public function handle(ShiftRotationEvent $event): void
    {
        $users = $event->employeeData;

        // Notify employees about their updated shift rotation
        Notification::send(
            $users,
            new ShiftRotationNotification(
                $event->dates,
                $event->rotationFrequency,
                $users
            )
        );
    }
}
