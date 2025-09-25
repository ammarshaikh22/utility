<?php

namespace App\Observers;

use App\Models\CurrencyFormatSetting;

class CurrencyFormatSettingObserver
{
    /**
     * Handle the "creating" event.
     *
     * This method runs automatically before a new CurrencyFormatSetting
     * record is inserted into the database.
     *
     * - If a company is available in the current session/context,
     *   the new currency format setting is assigned to that company
     *   by setting its `company_id`.
     */
    public function creating(CurrencyFormatSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
