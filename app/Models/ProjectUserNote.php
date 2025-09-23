<?php

namespace App\Models;

/**
 * App\Models\ProjectUserNote
 *
 * Represents the relation between a user and a project note.
 *
 * @property int $id Unique identifier for this record
 * @property int $user_id ID of the user associated with the note
 * @property int $project_note_id ID of the related project note
 * @property int|null $client_id Optional client ID
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectMember[] $members Related project members
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when this record was created
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when this record was last updated
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote newModelQuery() Creates a new model query
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote newQuery() Creates a new query builder
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote query() Returns a query builder for this model
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote whereCreatedAt($value) Filter by created_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote whereId($value) Filter by id
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote whereProjectNoteId($value) Filter by project_note_id
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote whereUpdatedAt($value) Filter by updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectUserNote whereUserId($value) Filter by user_id
 * @mixin \Eloquent
 */
class ProjectUserNote extends BaseModel
{
    // Specify the table explicitly (optional if using Laravel conventions)
    protected $table = 'project_user_notes';

    // Mass assignable fields
    protected $fillable = [
        'user_id',
        'project_note_id'
    ];

   
}
