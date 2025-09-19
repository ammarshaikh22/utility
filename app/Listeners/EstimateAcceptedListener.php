<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\EstimateAcceptedEvent;
use App\Notifications\EstimateAccepted;
use Illuminate\Support\Facades\Notification;

class EstimateAcceptedListener
{
    /**
     * Initialize the event listener.
     * This method is the constructor for the listener class and is currently empty.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the EstimateAcceptedEvent.
     * This method retrieves all admin users for the company associated with the estimate
     * and sends an estimate accepted notification to them when an estimate accepted event is triggered.
     *
     * @param EstimateAcceptedEvent $event The event containing the estimate data.
     * @return void
     */
    public function handle(EstimateAcceptedEvent $event)
    {
        $company = $event->estimate->company;
        Notification::send(User::allAdmins($company->id), new EstimateAccepted($event->estimate));
    }
}