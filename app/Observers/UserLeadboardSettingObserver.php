<?php

namespace App\Observers;

use App\Models\UserLeadboardSetting;

class UserLeadboardSettingObserver
{
    /**
     * Handle actions before a UserLeadboardSetting is created.
     * 
     * Sets the company_id of the model to the current company's ID if a company context exists.
     */
    public function creating(UserLeadboardSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
