<?php

namespace App\Observers;

use App\Models\UserTaskboardSetting;

class UserTaskboardSettingObserver
{
    /**
     * Handle actions before creating a UserTaskboardSetting.
     * 
     * - Sets the company_id based on the currently active company.
     */
    public function creating(UserTaskboardSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
