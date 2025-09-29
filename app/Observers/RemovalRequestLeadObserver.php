<?php

namespace App\Observers;

use App\Events\RemovalRequestAdminLeadEvent;
use App\Events\RemovalRequestApprovedRejectLeadEvent;
use App\Models\RemovalRequestLead;
use Exception;
use Illuminate\Support\Facades\Log;

class RemovalRequestLeadObserver
{

    /**
         * Handle the "created" event.
         * When a new RemovalRequestLead is created, notify admins 
         * by firing the RemovalRequestAdminLeadEvent.
     */
    public function created(RemovalRequestLead $removalRequestLead)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new RemovalRequestAdminLeadEvent($removalRequestLead));
        }
    }

     /**
         * Handle the "updated" event.
         * When a RemovalRequestLead is updated:
         *   - If the related lead exists, trigger RemovalRequestApprovedRejectLeadEvent.
         *   - Any exceptions are logged for debugging instead of breaking the app.
     */
    public function updated(RemovalRequestLead $removal)
    {
        if (!isRunningInConsoleOrSeeding()) {
            try {
                if ($removal->lead) {
                    event(new RemovalRequestApprovedRejectLeadEvent($removal));
                }
            } catch (Exception $e) {
                Log::info($e);
            }
        }
    }

    public function creating(RemovalRequestLead $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
