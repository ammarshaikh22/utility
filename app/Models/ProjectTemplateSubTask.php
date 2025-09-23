<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ProjectTemplateSubTask
 *
 * Represents a subtask within a project template task.
 * A subtask is a smaller unit of work that belongs to a parent task.
 *
 * @property int $id
 * @property int $project_template_task_id  // Foreign key to parent ProjectTemplateTask
 * @property string $title                  // Title/label of the subtask
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property string $status                 // Current status (e.g., pending, in progress, completed)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * Relationships:
 * @property-read \App\Models\ProjectTemplateTask $task
 *
 * @mixin \Eloquent
 */
class ProjectTemplateSubTask extends BaseModel
{
    /**
     * Cast date attributes into Carbon instances
     * for convenient date operations (comparisons, formatting, etc.).
     */
    protected $casts = [
        'start_date' => 'datetime',
        'due_date'   => 'datetime',
    ];

    /**
     * Relationship: Subtask belongs to a project template task.
     * Defines the parent task this subtask is associated with.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplateTask::class);
    }
}
