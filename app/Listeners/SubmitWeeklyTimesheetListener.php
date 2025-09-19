<?php

namespace App\Listeners;

use App\Events\SubmitWeeklyTimesheet;
use App\Notifications\NewTimesheetApproval;

/**
 * Listener for handling weekly timesheet submission events.
 *
 * - Notifies the reporting manager when an employee submits a timesheet.
 */
class SubmitWeeklyTimesheetListener
{
    /**
     * Handle the event.
     *
     * @param SubmitWeeklyTimesheet $event  The event instance containing the timesheet.
     * @return void
     */
    public function handle(SubmitWeeklyTimesheet $event): void
    {
        $reportingManager = $event->weeklyTimesheet->user->employeeDetails->reportingTo;

        // Notify reporting manager if assigned
        if ($reportingManager) {
            $reportingManager->notify(
                new NewTimesheetApproval($event->weeklyTimesheet)
            );
        }
    }
}
