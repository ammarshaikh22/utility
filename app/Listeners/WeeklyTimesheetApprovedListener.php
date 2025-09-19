<?php

namespace App\Listeners;

use App\Events\WeeklyTimesheetApprovedEvent;
use App\Notifications\WeeklyTimesheetApproved;

/**
 * Listener for handling weekly timesheet approval events.
 *
 * Notifies the user who submitted the timesheet when it gets approved.
 */
class WeeklyTimesheetApprovedListener
{
    /**
     * Handle the event.
     *
     * @param WeeklyTimesheetApprovedEvent $event
     * @return void
     */
    public function handle(WeeklyTimesheetApprovedEvent $event): void
    {
        $submitter = $event->weeklyTimesheet->user;

        if ($submitter) {
            $submitter->notify(new WeeklyTimesheetApproved($event->weeklyTimesheet));
        }
    }
}
