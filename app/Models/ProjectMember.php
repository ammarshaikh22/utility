<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\ProjectMember
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $hourly_rate
 * @property int|null $added_by
 * @property int|null $last_updated_by
 *
 * @property-read mixed $icon
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMember whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProjectMember extends Pivot
{
    use Notifiable;

    /**
     * Hidden fields when serializing the model.
     *
     * @var array<int, string>
     */
    protected $hidden = ['user_id', 'project_id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'project_members';

    /**
     * Always eager-load these relationships.
     *
     * @var array<int, string>
     */
    protected $with = ['user'];

    /**
     * Route notifications for mail channel.
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        return $this->user->email;
    }

    /**
     * Relationship: ProjectMember belongs to a user.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')
            ->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Relationship: ProjectMember belongs to a project.
     *
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get all active members of a project.
     *
     * @param int $id
     * @return \Illuminate\Support\Collection
     */
    public static function byProject($id)
    {
        return ProjectMember::join('users', 'users.id', '=', 'project_members.user_id')
            ->where('project_members.project_id', $id)
            ->where('users.status', 'active')
            ->get();
    }

    /**
     * Check if a user is a member of the given project.
     *
     * @param int $projectId
     * @param int $userId
     * @return ProjectMember|null
     */
    public static function checkIsMember($projectId, $userId)
    {
        return ProjectMember::where('project_id', $projectId)
            ->where('user_id', $userId)
            ->first();
    }
}
