<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Scopes\ActiveScope;

/**
 * App\Models\SubTask
 *
 * Represents a subtask associated with a main task in the system.
 *
 * @property int $id
 * @property int $task_id
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property string|null $start_date
 * @property string $status
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property int|null $assigned_to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User|null $assignedTo
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SubTaskFile[] $files
 * @property-read int|null $files_count
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTask whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SubTask extends BaseModel
{
    // Cast start_date and due_date to Carbon datetime instances automatically
    protected $casts = [
        'start_date' => 'datetime',
        'due_date' => 'datetime',
    ];

    // Eager load the assignedTo relationship by default
    protected $with = ['assignedTo'];

    /**
     * Relationship: SubTask belongs to a main Task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Relationship: SubTask is assigned to a User.
     * Bypasses the ActiveScope global scope to include inactive users if needed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Relationship: SubTask can have multiple files attached.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(SubTaskFile::class, 'sub_task_id');
    }
}
