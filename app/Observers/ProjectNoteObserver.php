<?php

namespace App\Observers;

use App\Events\ProjectNoteEvent;
use App\Events\ProjectNoteMentionEvent;
use App\Events\ProjectNoteUpdateEvent;
use App\Models\ProjectUserNote;
use App\Models\ProjectNote;
use App\Models\User;

class ProjectNoteObserver
{
    public function saving(ProjectNote $ProjectNote)
    {
        // Track who last updated
        if (!isRunningInConsoleOrSeeding()) {
            $ProjectNote->last_updated_by = user()->id;
        }
    }

    public function creating(ProjectNote $ProjectNote)
    {
        // Track who created
        if (!isRunningInConsoleOrSeeding()) {
            $ProjectNote->added_by = user()->id;
        }
    }

    public function created(ProjectNote $projectNote)
    {
        $project = $projectNote->project;

        // Handle mentions when creating a note
        if (request()->mention_user_id != null && request()->mention_user_id != '') {
            $projectNote->mentionUser()->sync(request()->mention_user_id);

            $projectUsers = json_decode($project->projectMembers->pluck('id'));
            $mentionIds = json_decode($projectNote->mentionNote->pluck('user_id'));
            $mentionUserId = array_intersect($mentionIds, $projectUsers);

            if ($mentionUserId != null && $mentionUserId != '') {
                event(new ProjectNoteMentionEvent($project, $projectNote->created_at, $mentionUserId));
            }

            $unmentionIds = array_diff($projectUsers, $mentionIds);
            if ($unmentionIds != null && $unmentionIds != '') {
                $projectNoteUsers = User::whereIn('id', $unmentionIds)->get();
                event(new ProjectNoteEvent($project, $projectNote->created_at, $projectNoteUsers));
            }
        } else {
            // If no mentions, notify based on note type
            if ($projectNote->type == 0) {
                event(new ProjectNoteEvent($project, $projectNote->created_at, $project->projectMembers));
            } else {
                $projectNoteUsers = User::whereIn('id', request()?->user_id)->get();
                event(new ProjectNoteEvent($project, $projectNote->created_at, $projectNoteUsers));
            }
        }
    }

    public function updating(ProjectNote $projectNote)
    {
        // Handle new mentions on update
        $mentionedUser = ProjectUserNote::where('project_note_id', $projectNote->id)
            ->pluck('user_id')->map(fn($id) => (string) $id)->toArray();

        $requestUserId = request()->user_id ?? [];
        $newMention = array_diff($requestUserId, $mentionedUser);
        $project = $projectNote->project;

        if (!empty($newMention) && $projectNote->type == '1') {
            event(new ProjectNoteMentionEvent($project, $projectNote->created_at, $newMention));
        }

        // Track title or detail changes
        $changes = [];

        if ($projectNote->isDirty('title')) {
            $changes['title'] = [
                'old' => $projectNote->getOriginal('title'),
                'new' => $projectNote->title
            ];
        }

        if ($projectNote->isDirty('details')) {
            $changes['details'] = [
                'old' => $projectNote->getOriginal('details'),
                'new' => $projectNote->details
            ];
        }

        // Notify users if important fields changed
        if (!empty($changes)) {
            $notifyUsers = collect();

            if ($projectNote->type == 0) {
                $notifyUsers = $project->projectMembers; // public note
            } else {
                $notifyUsers = User::whereIn('id', $requestUserId)->get(); // private note
            }

            if ($notifyUsers->isNotEmpty()) {
                event(new ProjectNoteUpdateEvent($project, $projectNote, $notifyUsers));
            }
        }
    }
}
