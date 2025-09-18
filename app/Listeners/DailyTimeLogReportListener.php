<?php

namespace App\Listeners;

use App\Events\DailyTimeLogReportEvent;
use App\Notifications\DailyTimeLogReport;
use Illuminate\Support\Facades\Notification;

class DailyTimeLogReportListener
{
    /**
     * Handle the DailyTimeLogReportEvent.
     * This method sends a daily time log report notification to the specified user,
     * including their role, using the DailyTimeLogReport notification class.
     *
     * @param DailyTimeLogReportEvent $event The event containing the user and role data.
     * @return void
     */
    public function handle(DailyTimeLogReportEvent $event): void
    {
        Notification::send($event->user, new DailyTimeLogReport($event->user, $event->role));
    }
}