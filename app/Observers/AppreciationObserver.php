<?php

namespace App\Observers;

use App\Events\AppreciationEvent;
use App\Models\Appreciation;
use App\Models\Notification;

/**
 * Class AppreciationObserver
 *
 * Observes the Appreciation model and handles model lifecycle events:
 * - Before create: attach company_id
 * - After create: dispatch event
 * - Before delete: clean up related notifications
 */
class AppreciationObserver
{
    /**
     * Handle the "creating" event.
     *
     * Automatically attach the current company_id
     * before persisting the model.
     *
    
     */
    public function creating(Appreciation $userAppreciation): void
    {
        if (company()) {
            $userAppreciation->company_id = company()->id;
        }
    }

    /**
     * Handle the "created" event.
     *
     * Dispatches an AppreciationEvent (e.g., to send notifications or trigger
     * broadcasts) after a new appreciation is saved, unless running
     * in console or seeding mode.
     *
    
     */
    public function created(Appreciation $userAppreciation): void
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new AppreciationEvent($userAppreciation, $userAppreciation->awardTo));
        }
    }

    /**
     * Handle the "deleting" event.
     *
     * Removes unread notifications related to this appreciation
     * to prevent leaving orphan notifications in the database.
     *
    
     */
    public function deleting(Appreciation $appreciation): void
    {
        Notification::where('type', 'App\Notifications\NewAppreciation')
            ->whereNull('read_at')
            ->where(function ($q) use ($appreciation) {
                $q->where('data', 'like', '{"id":' . $appreciation->id . ',%');
            })
            ->delete();
    }
}
