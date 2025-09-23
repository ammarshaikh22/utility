<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ProjectTemplateTask
 *
 * Represents a task within a project template. Each task can have multiple users
 * assigned, subtasks, and an optional category or milestone.
 *
 * @property int $id
 * @property string $heading                       // Task title or heading
 * @property string|null $description             // Optional description of the task
 * @property int $project_template_id             // Foreign key to parent ProjectTemplate
 * @property string $priority                      // Task priority (e.g., low, medium, high)
 * @property int|null $project_template_task_category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * Relationships:
 * @property-read \App\Models\ProjectTemplate $projectTemplate
 * @property-read \App\Models\ProjectTemplateMilestone|null $milestone
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTemplateSubTask[] $subtasks
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTemplateTaskUser[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $usersMany
 * @property-read \App\Models\TaskCategory|null $category
 *
 * @mixin \Eloquent
 */
class ProjectTemplateTask extends BaseModel
{
    /**
     * Cast attributes to desired types
     */
    protected $casts = [
        'task_labels' => 'array',  // Converts task_labels JSON to array automatically
    ];

    /**
     * Relationship: Task belongs to a ProjectTemplate
     */
    public function projectTemplate(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class);
    }

    /**
     * Relationship: Task may belong to a milestone in the template
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplateMilestone::class, 'milestone_id');
    }

    /**
     * Relationship: Task has many assigned users (pivot table records)
     */
    public function users(): HasMany
    {
        return $this->hasMany(ProjectTemplateTaskUser::class, 'project_template_task_id');
    }

    /**
     * Relationship: Task has many users through a pivot table
     */
    public function usersMany(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_template_task_users');
    }

    /**
     * Relationship: Task can have many subtasks
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(ProjectTemplateSubTask::class);
    }

    /**
     * Relationship: Task belongs to a category (optional)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class, 'project_template_task_category_id');
    }
}
