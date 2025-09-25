<?php

namespace App\Observers;

use App\Models\EmailNotificationSetting;

class EmailNotificationSettingObserver
{
    /**
     * Handle the "creating" event.
     * Automatically assigns the current company_id
     * whenever a new EmailNotificationSetting record is being created.
     */
    public function creating(EmailNotificationSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
