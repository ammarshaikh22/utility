<?php

namespace App\Observers;

use App\Models\EmployeeShift;
use App\Models\EmployeeShiftSchedule;
use Carbon\Carbon;

class EmployeeShiftObserver
{
    /**
     * Handle the "updating" event.
     * Updates existing shift schedules when the shift times are modified.
     */
    public function updating(EmployeeShift $employeeShift)
    {
        // Clear cached attendance settings
        session()->forget('attendance_setting');

        // Get all schedules for this shift from yesterday onwards
        $existingSchedules = EmployeeShiftSchedule::where('employee_shift_id', $employeeShift->id)
            ->whereDate('date', '>=', now()->subDay()->toDateString())
            ->get();

        if ($existingSchedules) {
            foreach ($existingSchedules as $item) {
                // Set new shift start time based on updated shift
                $item->shift_start_time = $item->date->toDateString() . ' ' . Carbon::parse($employeeShift->office_start_time)->toTimeString();

                // Handle overnight shifts
                if (Carbon::parse($employeeShift->office_start_time)->gt(Carbon::parse($employeeShift->office_end_time))) {
                    $item->shift_end_time = $item->date->addDay()->toDateString() . ' ' . Carbon::parse($employeeShift->office_end_time)->toTimeString();
                }
                else {
                    $item->shift_end_time = $item->date->toDateString() . ' ' . Carbon::parse($employeeShift->office_end_time)->toTimeString();
                }

                // Save changes without firing events
                $item->saveQuietly();
            }
        }
    }

    /**
     * Handle the "creating" event.
     * Sets the company_id for the new employee shift.
     */
    public function creating(EmployeeShift $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
