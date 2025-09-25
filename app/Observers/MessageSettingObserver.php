<?php

namespace App\Observers;

use App\Models\MessageSetting;

class MessageSettingObserver
{
    // Before creating a new MessageSetting record, attach it to the current company
    public function creating(MessageSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
