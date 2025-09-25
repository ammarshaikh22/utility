<?php

namespace App\Observers;

use App\Models\LeaveSetting;

class LeaveSettingObserver
{
    // Before creating a new LeaveSetting, assign it to the current company
    public function creating(LeaveSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
