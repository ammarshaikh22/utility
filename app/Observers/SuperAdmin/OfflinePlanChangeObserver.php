<?php

namespace App\Observers\SuperAdmin;

use App\Models\SuperAdmin\OfflinePlanChange;
use App\Events\SuperAdmin\OfflinePackageChangeRequestEvent;
use App\Events\SuperAdmin\OfflinePackageChangeConfirmationEvent;

class OfflinePlanChangeObserver
{
    // After creating an OfflinePlanChange, trigger a package change request event
    public function created(OfflinePlanChange $offlinePlanChange)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $company = company();
            event(new OfflinePackageChangeRequestEvent($company, $offlinePlanChange));
        }
    }

    // After updating an OfflinePlanChange, if the status changed, trigger a package change confirmation event
    public function updated(OfflinePlanChange $offlinePlanChange)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ($offlinePlanChange->isDirty('status')) {
                event(new OfflinePackageChangeConfirmationEvent($offlinePlanChange));
            }
        }
    }
}
