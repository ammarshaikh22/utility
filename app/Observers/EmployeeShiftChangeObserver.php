<?php

namespace App\Observers;

use App\Events\EmployeeShiftChangeEvent;
use App\Models\EmployeeShiftChangeRequest;
use App\Models\EmployeeShiftSchedule;

class EmployeeShiftChangeObserver
{
    /**
     * Handle the "created" event.
     * Fires an event when a new shift change request is created.
     */
    public function created(EmployeeShiftChangeRequest $changeRequest)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new EmployeeShiftChangeEvent($changeRequest));
        }
    }

    /**
     * Handle the "creating" event.
     * Sets the company_id for the new shift change request.
     */
    public function creating(EmployeeShiftChangeRequest $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    /**
     * Handle the "updated" event.
     * Updates the employee shift schedule if the request status is accepted,
     * and triggers an event to notify of status change.
     */
    public function updated(EmployeeShiftChangeRequest $changeRequest)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ($changeRequest->isDirty('status')) {

                if ($changeRequest->status == 'accepted') {
                    EmployeeShiftSchedule::where('id', $changeRequest->shift_schedule_id)
                        ->update(['employee_shift_id' => $changeRequest->employee_shift_id]);
                }

                event(new EmployeeShiftChangeEvent($changeRequest, 'statusChange'));
            }
        }
    }
}
