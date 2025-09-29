<?php

namespace App\Observers\SuperAdmin;

use App\Models\PackageUpdateNotify;
use App\Events\SuperAdmin\PackageUpdateNotifyEvent;

class PackageUpdateNotifyObserver
{
    // After creating a PackageUpdateNotify, trigger an event to notify about the package update
    public function created(PackageUpdateNotify $packageUpdateNotify)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new PackageUpdateNotifyEvent($packageUpdateNotify));
        }
    }
}
