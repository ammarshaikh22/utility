<?php

namespace App\Observers;

use App\Models\TicketEmailSetting;

class TicketEmailSettingObserver
{
    // Set company_id when creating a TicketEmailSetting record
    public function creating(TicketEmailSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
