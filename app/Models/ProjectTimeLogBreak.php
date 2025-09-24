<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\ProjectTimeLogBreak
 *
 * Represents a break entry within a project time log.
 *
 * @property int $id
 * @property int|null $project_time_log_id The related project time log ID
 * @property \Illuminate\Support\Carbon $start_time When the break started
 * @property \Illuminate\Support\Carbon|null $end_time When the break ended
 * @property string $reason Reason for the break
 * @property string|null $total_hours Total break hours as string
 * @property string|null $total_minutes Total break minutes as string
 * @property int|null $added_by User who added the break
 * @property int|null $last_updated_by User who last updated the break
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp of creation
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp of last update
 * @property int|null $company_id Company ID if applicable
 * @property-read \App\Models\ProjectTimeLog|null $timelog Related ProjectTimeLog model
 * @property-read \App\Models\Company|null $company Related Company model
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak newModelQuery() Creates a new Eloquent model query
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak newQuery() Creates a new query builder
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak query() Returns a query builder for this model
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereAddedBy($value) Filter by added_by
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereCreatedAt($value) Filter by created_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereEndTime($value) Filter by end_time
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereId($value) Filter by id
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereLastUpdatedBy($value) Filter by last_updated_by
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereProjectTimeLogId($value) Filter by project_time_log_id
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereReason($value) Filter by reason
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereStartTime($value) Filter by start_time
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereTotalHours($value) Filter by total_hours
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereTotalMinutes($value) Filter by total_minutes
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereUpdatedAt($value) Filter by updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTimeLogBreak whereCompanyId($value) Filter by company_id
 * @mixin \Eloquent
 */
class ProjectTimeLogBreak extends BaseModel
{
    use HasFactory, HasCompany;

    // Cast start_time and end_time as Carbon datetime objects
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Relation: The time log that this break belongs to.
     */
    public function timelog(): BelongsTo
    {
        return $this->belongsTo(ProjectTimeLog::class, 'project_time_log_id');
    }

    /**
     * Total break minutes for a specific project
     */
    public static function projectBreakMinutes($projectID)
    {
        return ProjectTimeLogBreak::join(
                'project_time_logs',
                'project_time_log_breaks.project_time_log_id',
                '=',
                'project_time_logs.id'
            )
            ->where('project_time_logs.project_id', $projectID)
            ->sum('project_time_log_breaks.total_minutes');
    }

    /**
     * Total break minutes for a specific task (only finished time logs)
     */
    public static function taskBreakMinutes($taskID)
    {
        return ProjectTimeLogBreak::join(
                'project_time_logs',
                'project_time_log_breaks.project_time_log_id',
                '=',
                'project_time_logs.id'
            )
            ->where('project_time_logs.task_id', $taskID)
            ->whereNotNull('project_time_logs.end_time')
            ->sum('project_time_log_breaks.total_minutes');
    }

    /**
     * Total break minutes for a specific user
     */
    public static function userBreakMinutes($userID)
    {
        return ProjectTimeLogBreak::join(
                'project_time_logs',
                'project_time_log_breaks.project_time_log_id',
                '=',
                'project_time_logs.id'
            )
            ->where('project_time_logs.user_id', $userID)
            ->sum('project_time_log_breaks.total_minutes');
    }

    /**
     * Total break minutes for a specific milestone
     */
    public static function milestoneBreakMinutes($milestoneID)
    {
        return ProjectTimeLogBreak::join(
                'project_time_logs',
                'project_time_log_breaks.project_time_log_id',
                '=',
                'project_time_logs.id'
            )
            ->join(
                'project_milestones',
                'project_milestones.project_id',
                '=',
                'project_time_logs.project_id'
            )
            ->where('project_milestones.id', $milestoneID)
            ->sum('project_time_log_breaks.total_minutes');
    }

    /**
     * Get breaks for a specific date, optionally for a specific user
     */
    public static function dateWiseTimelogBreak($date, $userID = null)
    {
        $timelogs = ProjectTimeLogBreak::join(
                'project_time_logs',
                'project_time_log_breaks.project_time_log_id',
                '=',
                'project_time_logs.id'
            )
            ->whereDate('project_time_log_breaks.start_time', $date)
            ->whereNotNull('project_time_logs.end_time')
            ->select('project_time_log_breaks.*');

        if (!is_null($userID)) {
            $timelogs = $timelogs->where('project_time_logs.user_id', $userID);
        }

        return $timelogs->get();
    }

    /**
     * Get total break minutes for a specific week, optionally for a specific user
     */
    public static function weekWiseTimelogBreak($startDate, $endDate, $userID = null)
    {
        $timelogs = ProjectTimeLogBreak::join(
                'project_time_logs',
                'project_time_log_breaks.project_time_log_id',
                '=',
                'project_time_logs.id'
            )
            ->whereBetween(DB::raw('DATE(project_time_log_breaks.`start_time`)'), [$startDate, $endDate])
            ->whereNotNull('project_time_logs.end_time')
            ->select('project_time_log_breaks.*');

        if (!is_null($userID)) {
            $timelogs = $timelogs->where('project_time_logs.user_id', $userID);
        }

        return $timelogs->sum('project_time_log_breaks.total_minutes');
    }
}
