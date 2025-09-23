<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\ProjectNote
 *
 * Represents a note associated with a project. Notes can be linked to users,
 * mentioned users, and optionally visible to clients. They can also require
 * a password for access depending on the configuration.
 *
 * @property int $id
 * @property int|null $project_id ID of the related project
 * @property string $title Title of the note
 * @property int $type Type/category of the note (custom-defined meaning)
 * @property int|null $client_id ID of the related client (if applicable)
 * @property int $is_client_show Flag if the note should be visible to clients
 * @property int $ask_password Flag if the note requires password protection
 * @property string $details Detailed description/content of the note
 * @property int|null $added_by User ID who created the note
 * @property int|null $last_updated_by User ID who last updated the note
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when created
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when updated
 *
 * @property-read \App\Models\Project $project Related project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectUserNote[] $members Users linked to this note
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $noteUsers Users directly related through pivot table
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $mentionUser Users mentioned in this note
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MentionUser[] $mentionNote Mention-user pivot records
 *
 * @mixin \Eloquent
 */
class ProjectNote extends BaseModel
{
    /**
     * Relation: Get project-user notes linked to this project note
     */
    public function members(): HasMany
    {
        return $this->hasMany(ProjectUserNote::class, 'project_note_id');
    }

    /**
     * Relation: Get the project this note belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Relation: Get users associated with this note
     * (via project_user_notes pivot table)
     */
    public function noteUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user_notes');
    }

    /**
     * Relation: Get users mentioned in this note
     * Uses custom MentionUser pivot model
     */
    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')
            ->withoutGlobalScope(ActiveScope::class)
            ->using(MentionUser::class);
    }

    /**
     * Relation: Get mention-user pivot records for this note
     */
    public function mentionNote(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'project_note_id');
    }
}
