<?php

namespace App\Listeners;

use App\Models\User;
use App\Scopes\ActiveScope;
use App\Events\NewProjectEvent;
use App\Notifications\NewProject;
use App\Notifications\NewProjectMember;
use App\Notifications\NewProjectStatus;
use App\Notifications\ProjectMemberMention;
use App\Notifications\ProjectRating;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class NewProjectListener
{
    /**
     * Handle the incoming project-related event.
     *
     * Depending on the project status and notification type,
     * this listener sends notifications to:
     *  - The client (if assigned)
     *  - Project members
     *  - A specific notified user
     *
     * @param NewProjectEvent $event  Event containing project and notification details.
     * @return void
     */
    public function handle(NewProjectEvent $event)
    {
        /**
         * 1. Notify client when a new project is created for them.
         */
        if ($event->project->client_id != null) {
            $clientId = $event->project->client_id;

            // Get client user (ignoring the ActiveScope so even inactive clients can be found).
            $notifyUsers = User::withoutGlobalScope(ActiveScope::class)->findOrFail($clientId);

            // Send notification only if project status matches.
            if (!is_null($notifyUsers) && $event->projectStatus === 'NewProjectClient') {
                Notification::send($notifyUsers, new NewProject($event->project));
            }
        }

        /**
         * 2. Notify project members about project status changes.
         */
        $projectMembers = $event->project->projectMembers;

        if ($event->projectStatus === 'statusChange') {
            // If notifyUser is a single user (not a collection), notify them directly.
            if (!is_null($event->notifyUser) && !($event->notifyUser instanceof Collection)) {
                $event->notifyUser->notify(new NewProjectStatus($event->project));
            }

            // Notify all project members.
            Notification::send($projectMembers, new NewProjectStatus($event->project));
        }

        /**
         * 3. Handle additional notification types.
         */
        if ($event->notificationName === 'NewProject') {
            // Notify a new project member.
            Notification::send($event->notifyUser, new NewProjectMember($event->project));

        } elseif ($event->notificationName === 'ProjectMention') {
            // Notify when a project member is mentioned.
            Notification::send($event->notifyUser, new ProjectMemberMention($event->project));

        } elseif ($event->notificationName === 'ProjectRating') {
            // Notify when a project rating is submitted.
            Notification::send($event->notifyUser, new ProjectRating($event->project));
        }
    }
}
