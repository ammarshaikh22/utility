<?php

namespace App\Observers;

use App\Models\CustomLinkSetting;

class CustomLinkSettingObserver
{
    /**
     * Handle the "creating" event for CustomLinkSetting.
     *
     * This method ensures that when a new CustomLinkSetting is being created,
     * it is automatically linked to the current company by assigning the
     * `company_id`.
     *
     * This enforces multi-tenancy, so each company only manages
     * its own custom link settings.
     */
    public function creating(CustomLinkSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
