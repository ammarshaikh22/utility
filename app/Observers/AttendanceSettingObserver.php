<?php

namespace App\Observers;

use App\Models\AttendanceSetting;

class AttendanceSettingObserver
{
    /**
     * Triggered when a new attendance setting is being created.
     * - Automatically assigns the current company_id.
     */
    public function creating(AttendanceSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
