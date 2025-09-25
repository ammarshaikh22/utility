<?php

namespace App\Observers;

use App\Models\ThemeSetting;

class ThemeSettingObserver
{
    // Set the company_id when creating a ThemeSetting
    public function creating(ThemeSetting $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }
}
