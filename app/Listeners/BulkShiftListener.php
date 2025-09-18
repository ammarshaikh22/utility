<?php

namespace App\Listeners;

use App\Events\BulkShiftEvent;
use App\Notifications\BulkShiftNotification;
use Illuminate\Support\Facades\Notification;

class BulkShiftListener
{
    /**
     * Handle the BulkShiftEvent.
     * This method sends a notification to the specified users about a bulk shift assignment,
     * including details about the users, date range, and the user ID responsible for the action.
     *
     * @param BulkShiftEvent $event The event containing user data, date range, and user ID.
     * @return void
     */
    public function handle(BulkShiftEvent $event)
    {
        Notification::send($event->userData, new BulkShiftNotification($event->userData, $event->dateRange, $event->userId));
    }
}