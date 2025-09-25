<?php

namespace App\Observers;

use App\Models\ModuleSetting;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;

class ModuleSettingObserver
{
    /**
     * Handle the "updated" event.
     * When a ModuleSetting is updated, clear the cached module permissions
     * for all users (ignoring ActiveScope and CompanyScope).
     *
     * @param ModuleSetting $model
     */
    //phpcs:ignore
    public function updated(ModuleSetting $model)
    {
        User::withoutGlobalScopes([ActiveScope::class, CompanyScope::class])->get()
            ->each(function ($user) {
                cache()->forget('user_modules_' . $user->id);
            });
    }
}
