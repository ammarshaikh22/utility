<?php

namespace App\Observers;

use App\Models\Attendance;

class AttendanceObserver
{
    /**
     * Triggered before saving (both creating and updating).
     * Sets the "last_updated_by" field to the current user.
     */
    public function saving(Attendance $attendance)
    {
        if (user()) {
            $attendance->last_updated_by = user()->id;
        }
    }

    /**
     * Triggered only when creating a new attendance record.
     * - Sets "added_by" to the current user.
     * - Copies work_from_type into working_from (unless it’s "other").
     * - Assigns the company_id based on the attendance’s user.
     */
    public function creating(Attendance $attendance)
    {
        if (user()) {
            $attendance->added_by = user()->id;
        }

        if ($attendance->work_from_type != 'other') {
            $attendance->working_from = $attendance->work_from_type;
        }

        $attendance->company_id = $attendance->user->company_id;
    }
}
