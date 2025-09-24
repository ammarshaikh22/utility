<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ProjectActivity
 *
 * @property int $id
 * @property int $project_id
 * @property string $activity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\Project $project
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity whereActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectActivity whereUpdatedAt($value)
 * 
 * @mixin \Eloquent
 */
class ProjectActivity extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'project_activity';

    /**
     * Get the project that owns the activity.
     *
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Retrieve project activities with optional user filter.
     *
     * @param  int       $projectId
     * @param  int       $limit
     * @param  int|null  $userID
     * @return \Illuminate\Support\Collection
     */
    public static function getProjectActivities($projectId, $limit, $userID = null)
    {
        $projectActivity = ProjectActivity::select(
            'project_activity.id',
            'project_activity.project_id',
            'project_activity.activity',
            'project_activity.created_at',
            'project_activity.updated_at'
        );

        if ($userID) {
            $projectActivity = $projectActivity
                ->join('projects', 'projects.id', '=', 'project_activity.project_id')
                ->join('project_members', 'project_members.project_id', '=', 'projects.id')
                ->where('project_members.user_id', '=', $userID);
        }

        return $projectActivity
            ->where('project_activity.project_id', $projectId)
            ->orderBy('project_activity.id', 'desc')
            ->limit($limit)
            ->get();
    }
}
