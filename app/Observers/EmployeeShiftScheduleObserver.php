<?php

namespace App\Observers;

use App\Events\EmployeeShiftScheduleEvent;
use App\Helper\Files;
use App\Models\EmployeeShift;
use App\Models\EmployeeShiftSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class EmployeeShiftScheduleObserver
{
    // Flag to check if the schedule change is part of a shift rotation
    public static $isShiftRotation = false;

    /**
     * Handle the "saving" event.
     * Updates the last_updated_by and remarks before saving.
     */
    public function saving(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        if (user()) {
            $employeeShiftSchedule->last_updated_by = user()->id;
            $employeeShiftSchedule->remarks = request()->remarks;
        }
    }

    /**
     * Handle the "creating" event.
     * Sets added_by, calculates shift start/end time, and stores remarks.
     */
    public function creating(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        if (user()) {
            $employeeShiftSchedule->added_by = user()->id;
            $employeeShiftSchedule->shift_start_time = $employeeShiftSchedule->date->toDateString() . ' ' . $employeeShiftSchedule->shift->office_start_time;

            // Handle overnight shifts
            if (Carbon::parse($employeeShiftSchedule->shift->office_start_time)->gt(Carbon::parse($employeeShiftSchedule->shift->office_end_time))) {
                $employeeShiftSchedule->shift_end_time = $employeeShiftSchedule->date->addDay()->toDateString() . ' ' . $employeeShiftSchedule->shift->office_end_time;
            }
            else {
                $employeeShiftSchedule->shift_end_time = $employeeShiftSchedule->date->toDateString() . ' ' . $employeeShiftSchedule->shift->office_end_time;
            }

            $employeeShiftSchedule->remarks = request()->remarks;
        }
    }

    /**
     * Handle the "created" event.
     * Fires an event unless part of a rotation and uploads attached files.
     */
    public function created(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        if (user() && !self::$isShiftRotation) {
            event(new EmployeeShiftScheduleEvent($employeeShiftSchedule));
        }

        // Handle file upload if provided
        if (request()->hasFile('file')) {
            Files::deleteFile(request()->file, 'employee-shift-file/' . $employeeShiftSchedule->id);
            $fileName = Files::uploadLocalOrS3(request()->file, 'employee-shift-file/' . $employeeShiftSchedule->id);

            $employeeShiftSchedule->file = $fileName;
            $employeeShiftSchedule->saveQuietly();
        }
    }

    /**
     * Handle the "updating" event.
     * Updates last_updated_by and recalculates shift start/end times.
     */
    public function updating(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        if (user()) {
            $employeeShiftSchedule->last_updated_by = user()->id;
        }

        // Determine the correct shift for time calculation
        if (!isRunningInConsoleOrSeeding() && user() && request()->employee_shift_id) {
            $shift = EmployeeShift::findOrFail(request()->employee_shift_id);
        } else {
            $shift = EmployeeShift::findOrFail($employeeShiftSchedule->employee_shift_id);
        }

        // Update shift start and end times
        $employeeShiftSchedule->shift_start_time = $employeeShiftSchedule->date->toDateString() . ' ' . $shift->office_start_time;

        if (Carbon::parse($shift->office_start_time)->gt(Carbon::parse($shift->office_end_time))) {
            $employeeShiftSchedule->shift_end_time = $employeeShiftSchedule->date->addDay()->toDateString() . ' ' . $shift->office_end_time;
        } else {
            $employeeShiftSchedule->shift_end_time = $employeeShiftSchedule->date->toDateString() . ' ' . $shift->office_end_time;
        }
    }

    /**
     * Handle the "updated" event.
     * Fires an event if the shift has changed.
     */
    public function updated(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        if (user() && $employeeShiftSchedule->isDirty('employee_shift_id')) {
            event(new EmployeeShiftScheduleEvent($employeeShiftSchedule));
        }
    }

    /**
     * Handle the "deleting" event.
     * Deletes attached files and directories.
     */
    public function deleting(EmployeeShiftSchedule $employeeShiftSchedule)
    {
        if ($employeeShiftSchedule->file) {
            Files::deleteFile($employeeShiftSchedule->file, 'employee-shift-file/' . $employeeShiftSchedule->id);
            Files::deleteDirectory('employee-shift-file/' . $employeeShiftSchedule->id);
        }
    }
}
