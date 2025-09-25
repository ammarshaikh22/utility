<?php

namespace App\Observers;

use App\Events\RemovalRequestAdminEvent;
use App\Events\RemovalRequestApproveRejectEvent;
use App\Models\RemovalRequest;
use Exception;
use Illuminate\Support\Facades\Log;

class RemovalRequestObserver
{
    /**
     * Handle the "creating" event.
     * Automatically sets the company_id when a new RemovalRequest is being created.
     */
    public function creating(RemovalRequest $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

    /**
     * Handle the "created" event.
     * After a new RemovalRequest is created, fire an event
     * to notify admins about the new request.
     */
    public function created(RemovalRequest $removalRequest)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new RemovalRequestAdminEvent($removalRequest));
        }
    }

    /**
     * Handle the "updated" event.
     * When a RemovalRequest is updated:
     *   - If the request is linked to a user
     *   - And its status has changed from "pending"
     *   - Then fire an event to notify about approval or rejection.
     *
     * Any exceptions encountered are logged instead of breaking the application.
     */
    public function updated(RemovalRequest $removal)
    {
        if (!isRunningInConsoleOrSeeding()) {
            try {
                if ($removal->user) {
                    if ($removal->status != 'pending') {
                        event(new RemovalRequestApproveRejectEvent($removal));
                    }
                }
            } catch (Exception $e) {
                // Log the error for debugging without stopping execution
                Log::info($e);
            }
        }
    }
}
