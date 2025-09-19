<?php

namespace App\Listeners;

use App\Events\EmployeeShiftScheduleEvent;
use App\Notifications\ShiftScheduled;
use Illuminate\Support\Facades\Notification;

class EmployeeShiftScheduleListener
{
    /**
     * Handle the EmployeeShiftScheduleEvent.
     * This method sends a shift scheduled notification to the user associated with the shift schedule
     * when an employee shift schedule event is triggered, using the ShiftScheduled notification class.
     *
     * @param EmployeeShiftScheduleEvent $event The event containing the employee shift schedule data.
     * @return void
     */
    public function handle(EmployeeShiftScheduleEvent $event)
    {
        Notification::send($event->employeeShiftSchedule->user, new ShiftScheduled($event->employeeShiftSchedule));
    }
}