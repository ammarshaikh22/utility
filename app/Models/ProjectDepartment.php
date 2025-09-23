<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\ProjectDepartment
 *
 * @property int $project_id
 * @property int $team_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\Team $department
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDepartment whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProjectDepartment extends Pivot
{
    /**
     * The database table used by the pivot.
     *
     * @var string
     */
    protected $table = 'project_departments';

    /**
     * The attributes that should be hidden for arrays/JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = ['project_id', 'team_id'];

    /**
     * Get the project for this pivot record.
     *
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the department (team) for this pivot record.
     *
     * @return BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}
