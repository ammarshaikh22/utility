<?php

namespace App\Listeners;

use App\Events\WeeklyTimesheetDraftEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\WeeklyTimesheetRejected;

class WeeklyTimesheetDraftListener
{
    /**
     * Constructor for the event listener.
     * 
     * This method is automatically called when the listener is instantiated.
     * Currently, it doesn't perform any actions.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the WeeklyTimesheetDraftEvent.
     * 
     * This method is executed when a WeeklyTimesheetDraftEvent is fired.
     * It retrieves the user who submitted the timesheet and sends them a
     * WeeklyTimesheetRejected notification.
     *
     * @param WeeklyTimesheetDraftEvent $event The event instance containing the timesheet data.
     */
    public function handle(WeeklyTimesheetDraftEvent $event): void
    {
        // Get the user who submitted the weekly timesheet
        $submitBy = $event->weeklyTimesheet->user;

        // If the user exists, notify them that their timesheet was rejected
        if ($submitBy) {
            $submitBy->notify(new WeeklyTimesheetRejected($event->weeklyTimesheet));
        }
    }
}
