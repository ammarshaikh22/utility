<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ProjectTemplate
 *
 * Represents a reusable template for creating projects.
 * Templates can have categories, clients, members, tasks, and milestones.
 *
 * @property int $id
 * @property string $project_name
 * @property int|null $category_id
 * @property int|null $client_id
 * @property int|null $company_id
 * @property int $added_by
 * @property string|null $project_summary
 * @property string|null $notes
 * @property string|null $feedback
 * @property string $client_view_task
 * @property string $allow_client_notification
 * @property string $manual_timelog
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\ProjectCategory|null $category
 * @property-read \App\Models\User|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTemplateMember[] $members
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTemplateTask[] $tasks
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTemplateMilestone[] $milestones
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $projectMembers
 * @property-read \App\Models\Company|null $company
 *
 * @mixin \Eloquent
 */
class ProjectTemplate extends BaseModel
{
    use CustomFieldsTrait, HasCompany;

    /**
     * Parent category of the project template.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class);
    }

    /**
     * Client associated with the project template.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class)->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Members assigned to the project template.
     */
    public function members(): HasMany
    {
        return $this->hasMany(ProjectTemplateMember::class);
    }

    /**
     * Tasks associated with the project template.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTemplateTask::class, 'project_template_id')->orderByDesc('id');
    }

    /**
     * Milestones associated with the project template.
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectTemplateMilestone::class, 'project_template_id')->orderByDesc('id');
    }

    /**
     * Check if the current logged-in user is a project member.
     */
    public function checkProjectUser(): bool
    {
        return ProjectTemplateMember::where('project_template_id', $this->id)
            ->where('user_id', user()->id)
            ->exists();
    }

    /**
     * Check if the current logged-in user is the project client.
     *
     * ⚠️ NOTE: You used `ProjectTemplateMember::where('id', $this->id)` — 
     * that seems like a bug. Should probably query `ProjectTemplate` instead.
     */
    public function checkProjectClient(): bool
    {
        return ProjectTemplate::where('id', $this->id)
            ->where('client_id', user()->id)
            ->exists();
    }

    /**
     * Get all projects for a specific client.
     */
    public static function clientProjects($clientId)
    {
        return ProjectTemplate::where('client_id', $clientId)->get();
    }

    /**
     * Get all projects assigned to a specific employee.
     */
    public static function byEmployee($employeeId)
    {
        return ProjectTemplate::join(
                'project_template_members',
                'project_template_members.project_template_id',
                '=',
                'project_templates.id'
            )
            ->where('project_template_members.user_id', $employeeId)
            ->get();
    }

    /**
     * Many-to-Many relationship with project members (users).
     */
    public function projectMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_template_members');
    }
}
