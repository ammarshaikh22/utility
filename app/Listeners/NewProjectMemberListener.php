<?php

namespace App\Listeners;

use App\Events\NewProjectMemberEvent;
use App\Notifications\NewProjectMember;
use Illuminate\Support\Facades\Notification;

class NewProjectMemberListener
{
    /**
     * Handle the event when a new project member is added.
     *
     * This listener ensures that the newly added project member
     * receives a notification about their assignment.
     *
     * @param NewProjectMemberEvent $event  Event containing the project member details.
     * @return void
     */
    public function handle(NewProjectMemberEvent $event)
    {
        // Notify the user who has just been added to the project.
        Notification::send(
            $event->projectMember->user,
            new NewProjectMember($event->projectMember->project)
        );
    }
}
