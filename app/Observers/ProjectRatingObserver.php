<?php

namespace App\Observers;

use App\Events\RatingEvent;
use App\Models\Notification;
use App\Models\ProjectRating;

class ProjectRatingObserver
{
    /**
     * Handle the "created" event for ProjectRating.
     * 
     * This method is triggered when a new rating is created.
     * It sends a notification (via RatingEvent) that a rating has been added.
     */
    public function created(ProjectRating $rating)
    {
        if (!isRunningInConsoleOrSeeding()) {
            // Fire an event to notify the user that a new rating has been added
            event(new RatingEvent($rating, 'add'));
        }
    }

    /**
     * Handle the "deleting" event for ProjectRating.
     * 
     * This method is triggered when a rating is being deleted.
     * It sends a notification (via RatingEvent) that a rating has been updated/removed,
     * and deletes any existing notifications related to this rating.
     */
    public function deleting(ProjectRating $rating)
    {
        if (!isRunningInConsoleOrSeeding()) {
            // Fire an event to notify the user that a rating has been updated (removed)
            event(new RatingEvent($rating, 'update'));
        }

        // Remove the related notifications for this rating
        $notifyData = ['App\Notifications\RatingUpdate'];
        Notification::deleteNotification($notifyData, $rating->id);
    }
}
