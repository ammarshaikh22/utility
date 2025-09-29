<?php

namespace App\Observers;

use App\Models\GlobalSetting;

class GlobalSettingObserver
{
    /**
     * Handle the "saving" event.
     * This is triggered before a GlobalSetting is saved.
     * Clears the cache for 'global_setting' to ensure fresh data.
     */
    public function saving(GlobalSetting $model)
    {
        cache()->forget('global_setting');
        return $model;
    }

    /**
     * Handle the "updated" event.
     * This is triggered after a GlobalSetting is updated.
     * Clears the cache for 'global_setting' to reflect the latest changes.
     */
    public function updated(GlobalSetting $model)
    {
        cache()->forget('global_setting');
        return $model;
    }

    /**
     * Handle the "deleted" event.
     * This is triggered after a GlobalSetting is deleted.
     * Clears the cache for 'global_setting' to prevent stale data.
     */
    public function deleted(GlobalSetting $model)
    {
        cache()->forget('global_setting');
        return $model;
    }
}
