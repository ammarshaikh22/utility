<?php

namespace App\Listeners;

use App\Events\EstimateDeclinedEvent;
use App\Notifications\EstimateDeclined;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class EstimateDeclinedListener
{
    /**
     * Handle the EstimateDeclinedEvent.
     * This method retrieves all admin users for the company associated with the estimate
     * and sends an estimate declined notification to them when an estimate declined event is triggered.
     *
     * @param EstimateDeclinedEvent $event The event containing the estimate data.
     * @return void
     */
    public function handle(EstimateDeclinedEvent $event)
    {
        $company = $event->estimate->company;
        Notification::send(User::allAdmins($company->id), new EstimateDeclined($event->estimate));
    }
}