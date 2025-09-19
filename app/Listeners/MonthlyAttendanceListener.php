<?php

namespace App\Listeners;

use App\Events\MonthlyAttendanceEvent;
use App\Notifications\MonthlyAttendance;
use Illuminate\Support\Facades\Notification;

class MonthlyAttendanceListener
{
    /**
     * Handle the MonthlyAttendanceEvent.
     * This method sends a monthly attendance notification to the specified user
     * when a monthly attendance event is triggered, using the MonthlyAttendance notification class.
     *
     * @param MonthlyAttendanceEvent $event The event containing the user data.
     * @return void
     */
    public function handle(MonthlyAttendanceEvent $event)
    {
        Notification::send($event->user, new MonthlyAttendance($event->user));
    }
}