<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use App\Helper\UserService;

/**
 * App\Models\Task
 *
 * Represents a task in the system, including relationships with users,
 * subtasks, files, comments, labels, milestones, time logs, and permissions.
 */
class Task extends BaseModel
{
    use Notifiable, SoftDeletes;   // Notifications and soft deletes
    use CustomFieldsTrait;         // Custom fields for tasks
    use HasCompany;                // Associate task with a company

    protected $casts = [
        'due_date' => 'datetime',
        'completed_on' => 'datetime',
        'start_date' => 'datetime',
    ];

    protected $appends = ['due_on', 'create_on']; // Computed attributes
    protected $guarded = ['id']; // Prevent mass assignment for 'id'
    protected $with = [
        'company:id,date_format',
        'project:id,project_name,need_approval_by_admin,project_short_code',
        'users:id,name,image'
    ];

    const CUSTOM_FIELD_MODEL = 'App\Models\Task';

    /* -------------------- RELATIONSHIPS -------------------- */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'recurring_task_id');
    }

    public function activeProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function label(): HasMany
    {
        return $this->hasMany(TaskLabel::class, 'task_id');
    }

    public function boardColumn(): BelongsTo
    {
        return $this->belongsTo(TaskboardColumn::class, 'board_column_id');
    }

    public function dependentTask()
    {
        return $this->belongsTo(Task::class, 'dependent_task_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_users')->withoutGlobalScope(ActiveScope::class)->using(TaskUser::class);
    }

    public function taskUsers(): HasMany
    {
        return $this->hasMany(TaskUser::class, 'task_id');
    }

    public function activeUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_users')->using(TaskUser::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(TaskLabelList::class, 'task_labels', 'task_id', 'label_id');
    }

    public function createBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScope(ActiveScope::class);
    }

    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class, 'task_category_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(SubTask::class, 'task_id');
    }

    public function completedSubtasks(): HasMany
    {
        return $this->hasMany(SubTask::class, 'task_id')->where('sub_tasks.status', 'complete');
    }

    public function incompleteSubtasks(): HasMany
    {
        return $this->hasMany(SubTask::class, 'task_id')->where('sub_tasks.status', 'incomplete');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'task_id')->orderByDesc('id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(TaskNote::class, 'task_id')->orderByDesc('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(TaskFile::class, 'task_id')->orderByDesc('id');
    }

    public function activeTimer(): HasOne
    {
        return $this->hasOne(ProjectTimeLog::class, 'task_id')->whereNull('project_time_logs.end_time');
    }

    public function userActiveTimer(): HasOne
    {
        return $this->hasOne(ProjectTimeLog::class, 'task_id')
            ->whereNull('project_time_logs.end_time')
            ->where('project_time_logs.user_id', user()->id);
    }

    public function activeTimerAll(): HasMany
    {
        return $this->hasMany(ProjectTimeLog::class, 'task_id')->whereNull('project_time_logs.end_time');
    }

    public function timeLogged(): HasMany
    {
        return $this->hasMany(ProjectTimeLog::class, 'task_id');
    }

    public function approvedTimeLogs(): HasMany
    {
        return $this->hasMany(ProjectTimeLog::class, 'task_id')->where('project_time_logs.approved', 1)->orderBy('project_time_logs.start_time', 'desc');
    }

    public function recurrings(): HasMany
    {
        return $this->hasMany(Task::class, 'recurring_task_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    public function mentionUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mention_users')->withoutGlobalScope(ActiveScope::class)->using(MentionUser::class);
    }

    public function mentionTask(): HasMany
    {
        return $this->hasMany(MentionUser::class, 'task_id');
    }

    /* -------------------- ATTRIBUTES -------------------- */

    public function getDueOnAttribute()
    {
        if (is_null($this->due_date)) return '';
        if (is_null($this->company_id)) return $this->due_date->format('Y-m-d');
        return $this->due_date->format($this->company->date_format);
    }

    public function getCreateOnAttribute()
    {
        if (is_null($this->start_date)) return '';
        return $this->start_date->format($this->company->date_format);
    }

    public function getIsTaskUserAttribute()
    {
        if (user()) return $this->taskUsers->where('user_id', user()->id)->first();
    }

    public function getTotalEstimatedMinutesAttribute()
    {
        return ($this->estimate_hours * 60) + $this->estimate_minutes;
    }

    /* -------------------- PERMISSIONS -------------------- */

    // Permission check: has all permission
    public function hasAllPermission($permission): bool
    {
        return $permission == 'all';
    }

    // Permission check: added tasks only
    public function hasAddedPermission($permission): bool
    {
        $userId = UserService::getUserId();
        $id = user()->id;

        if (in_array('client', user_roles())) {
            $clientContact = ClientContact::where('client_id', user()->id)->first();
            if ($clientContact) {
                $id = $clientContact->user_id;
            }
        }

        return $permission == 'added' && ($id == $this->added_by || $userId == $this->added_by);
    }

    // Permission check: owned tasks
    public function hasOwnedPermission($permission): bool
    {
        $taskUsers = $this->users->pluck('id')->toArray();
        $userId = UserService::getUserId();
        return $permission == 'owned' && (in_array(user()->id, $taskUsers) || in_array($userId, $taskUsers) || in_array('client', user_roles()));
    }

    // Permission check: both added and owned
    public function hasBothPermission($permission): bool
    {
        $taskUsers = $this->users->pluck('id')->toArray();
        $userId = UserService::getUserId();
        $id = user()->id;

        if (in_array('client', user_roles())) {
            $clientContact = ClientContact::where('client_id', user()->id)->first();
            if ($clientContact) {
                $id = $clientContact->user_id;
            }
        }

        return $permission == 'both' && (in_array(user()->id, $taskUsers) || ($this->added_by == $id || $this->added_by == $userId) || in_array('client', user_roles()));
    }

    // Check if the current user is project admin
    public function projectAdmin(): bool
    {
        return $this->project_admin === user()->id;
    }

    // Can view task
    public function canViewTicket(): bool
    {
        return $this->hasPermission(user()->permission('view_tasks'));
    }

    // Can delete task
    public function canDeleteTicket(): bool
    {
        return $this->hasPermission(user()->permission('delete_tasks'));
    }

    // Can edit task
    public function canEditTicket(): bool
    {
        return $this->hasPermission(user()->permission('edit_tasks'));
    }

    // General permission checker
    public function hasPermission($permission): bool
    {
        return $this->hasAllPermission($permission) ||
            $this->hasAddedPermission($permission) ||
            $this->hasOwnedPermission($permission) ||
            $this->hasBothPermission($permission) ||
            $this->projectAdmin();
    }
}
