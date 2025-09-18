<?php

namespace App\Listeners\SuperAdmin;

use App\Models\Company;
use Illuminate\Support\Facades\Notification;
use App\Events\SuperAdmin\PackageUpdateNotifyEvent;
use App\Notifications\SuperAdmin\PackageEmployeeIssue;

class PackageUpdateNotifyListener
{
    /**
     * Handle the PackageUpdateNotifyEvent.
     *
     * This method is triggered when a package update occurs that requires notifying
     * the company's employees. It retrieves the first active admin of the company
     * and sends them a notification about the package update issue.
     *
     * @param PackageUpdateNotifyEvent $event The event instance containing package update details.
     * @return void
     */
    public function handle(PackageUpdateNotifyEvent $event)
    {
        // Get the first active admin of the company associated with the package update
        $notifyUser = Company::firstActiveAdmin($event->packageUpdateNotify->company);

        // Send the PackageEmployeeIssue notification to the retrieved user
        Notification::send($notifyUser, new PackageEmployeeIssue($event->packageUpdateNotify));
    }
}
