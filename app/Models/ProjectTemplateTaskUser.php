<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ProjectTemplateTaskUser
 *
 * Represents the assignment of a user to a project template task.
 *
 * @property int $id
 * @property int $project_template_task_id   // Foreign key to ProjectTemplateTask
 * @property int $user_id                     // Foreign key to User
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * Relationships:
 * @property-read \App\Models\ProjectTemplateTask $task   // The task this user is assigned to
 * @property-read \App\Models\User $user                  // The assigned user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser whereProjectTemplateTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTemplateTaskUser whereUserId($value)
 *
 * @mixin \Eloquent
 */
class ProjectTemplateTaskUser extends BaseModel
{
    /**
     * Protect the ID from mass assignment
     */
    protected $guarded = ['id'];

    /**
     * Relationship: Task assignment belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')
                    ->withoutGlobalScope(ActiveScope::class); // Ignore active scope
    }

    /**
     * Relationship: Task assignment belongs to a project template task
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplateTask::class, 'project_template_task_id');
    }
}
