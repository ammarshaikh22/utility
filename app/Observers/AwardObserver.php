<?php

namespace App\Observers;

use App\Models\Award;
use App\Models\Notification;

class AwardObserver
{
    /**
     * Triggered when a new award is being created.
     * - Assigns the current company_id automatically.
     */
    public function creating(Award $appreciation)
    {
        if (company()) {
            $appreciation->company_id = company()->id;
        }
    }

    /**
     * Triggered when an award is being deleted.
     * - Loops through related appreciations.
     * - Removes unread notifications linked to each appreciation.
     */
    public function deleting(Award $award)
    {
        foreach ($award->appreciations as $appreciations) {
            Notification::where('type', 'App\Notifications\NewAppreciation')
                ->whereNull('read_at')
                ->where(function ($q) use ($appreciations) {
                    $q->where('data', 'like', '{"id":' . $appreciations->id . ',%');
                })->delete();
        }
    }
}
