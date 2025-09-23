<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\ProjectTimeLog
 *
 * Represents a log of time tracked by users for tasks and projects.
 *
 * @property int $id
 * @property string $start
 * @property string $name
 * @property int|null $project_id
 * @property int|null $task_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property string $memo
 * @property string|null $total_hours
 * @property int|null $total_minutes
 * @property int|null $edited_by_user
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $hourly_rate
 * @property float $earnings
 * @property int $approved
 * @property int|null $approved_by
 * @property int|null $invoice_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User|null $editor
 * @property-read mixed $duration
 * @property-read mixed $hours
 * @property-read mixed $icon
 * @property-read mixed $timer
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\Task|null $task
 * @property-read \App\Models\User $user
 * @property string|null $total_break_minutes
 * @property-read \App\Models\ProjectTimeLogBreak|null $activeBreak
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectTimeLogBreak[] $breaks
 * @property-read int|null $breaks_count
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $extras
 * @property-read mixed $hours_only
 * @mixin \Eloquent
 */
class ProjectTimeLog extends BaseModel
{
    use Notifiable, CustomFieldsTrait, HasCompany;

    // Cast start_time and end_time to Carbon datetime instances
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Always load these relationships
    protected $with = ['breaks', 'activeBreak'];

    const CUSTOM_FIELD_MODEL = 'App\Models\ProjectTimeLog';

    /**
     * Relation: The user who logged this time.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Relation: User who edited this time log.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by_user')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Relation: Associated project (even if soft-deleted)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }

    /**
     * Relation: Associated task (even if soft-deleted)
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id')->withTrashed();
    }

    /**
     * Relation: Task only if it is soft-deleted
     */
    public function tasksOnlyTrashed(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id')->onlyTrashed();
    }

    /**
     * Relation: All breaks associated with this time log
     */
    public function breaks(): HasMany
    {
        return $this->hasMany(ProjectTimeLogBreak::class, 'project_time_log_id');
    }

    /**
     * Relation: The currently active break (if any)
     */
    public function activeBreak(): HasOne
    {
        return $this->hasOne(ProjectTimeLogBreak::class, 'project_time_log_id')->whereNull('end_time');
    }

    // Append computed attributes
    protected $appends = ['hours', 'duration', 'timer', 'hours_only'];

    /**
     * Compute the total duration from start_time to now or end_time
     */
    public function getDurationAttribute()
    {
        $finishTime = now();

        if (!is_null($this->start_time)) {
            return $finishTime->diff($this->start_time)->format('%d days %H Hrs %i Mins %s Secs');
        }

        return '';
    }

    /**
     * Compute hours in a human-readable format
     */
    public function getHoursAttribute()
    {
        if (is_null($this->end_time)) {
            $totalMinutes = (($this->activeBreak) 
                ? $this->activeBreak->start_time->diffInMinutes($this->start_time) 
                : now()->diffInMinutes($this->start_time)) 
                - $this->breaks->sum('total_minutes');
        } else {
            $totalMinutes = $this->total_minutes - $this->breaks->sum('total_minutes');
        }

        return CarbonInterval::formatHuman($totalMinutes);
    }

    /**
     * Compute hours only (HH hrs MM mins)
     */
    public function getHoursOnlyAttribute()
    {
        $ids = is_string($this->ids) ? explode(',', $this->ids) : (array) $this->ids;
        $breakMinutes = ProjectTimeLogBreak::whereIn('project_time_log_id', $ids)->sum('total_minutes') ?? 0;

        if (is_null($this->end_time)) {
            $totalMinutes = (($this->activeBreak) 
                ? $this->activeBreak->start_time->diffInMinutes($this->start_time) 
                : now()->diffInMinutes($this->start_time)) 
                - $breakMinutes;
        } else {
            $totalMinutes = $this->total_minutes - $breakMinutes;
        }

        $hours = floor($totalMinutes / 60);
        $minutes = ($totalMinutes % 60);

        return sprintf('%02d' . __('app.hrs') . ' %02d' . __('app.mins'), $hours, $minutes);
    }

    /**
     * Compute timer string (HH:MM:SS)
     */
    public function getTimerAttribute()
    {
        $finishTime = $this->activeBreak ? $this->activeBreak->start_time : now();
        $startTime = Carbon::parse($this->start_time);

        $minutes = $finishTime->diffInMinutes($startTime) - $this->breaks->sum('total_minutes');
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;
        $secs = $finishTime->diffInSeconds($startTime) % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    /**
     * Get time logs for a specific date
     */
    public static function dateWiseTimelogs($date, $userID = null)
    {
        $timelogs = ProjectTimeLog::with('breaks')->whereDate('start_time', $date);

        if (!is_null($userID)) {
            $timelogs = $timelogs->where('user_id', $userID);
        }

        return $timelogs->get();
    }

    /**
     * Sum total minutes in a week for a user
     */
    public static function weekWiseTimelogs($startDate, $endDate, $userID = null)
    {
        $timelogs = ProjectTimeLog::whereBetween(DB::raw('DATE(`start_time`)'), [$startDate, $endDate]);

        if (!is_null($userID)) {
            $timelogs = $timelogs->where('user_id', $userID);
        }

        return $timelogs->sum('total_minutes');
    }

    /**
     * Get all active timers for a project
     */
    public static function projectActiveTimers($projectId)
    {
        return ProjectTimeLog::with('user')->whereNull('end_time')
            ->where('project_id', $projectId)
            ->get();
    }

    /**
     * Get all active timers for a task
     */
    public static function taskActiveTimers($taskId)
    {
        return ProjectTimeLog::with('user')->whereNull('end_time')
            ->where('task_id', $taskId)
            ->get();
    }

    /**
     * Get total project hours
     */
    public static function projectTotalHours($projectId)
    {
        return ProjectTimeLog::where('project_id', $projectId)
            ->sum('total_hours');
    }

    /**
     * Get total project minutes
     */
    public static function projectTotalMinuts($projectId)
    {
        return ProjectTimeLog::where('project_id', $projectId)
            ->sum('total_minutes');
    }

    /**
     * Get active timer for a specific member
     */
    public static function memberActiveTimer($memberId)
    {
        return ProjectTimeLog::with('project')->where('user_id', $memberId)
            ->whereNull('end_time')
            ->first();
    }

    /**
     * Get currently active timer for the logged-in user
     */
    public static function selfActiveTimer()
    {
        return ProjectTimeLog::with('activeBreak')
            ->where('user_id', user()->id)
            ->whereNull('end_time')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Get all active timers for the logged-in user
     * 💡 This is the method you emphasized — NOT removed
     */
    public static function totalActiveTimer()
    {
        return ProjectTimeLog::with('activeBreak')
            ->where('user_id', user()->id)
            ->whereNull('end_time')
            ->orderByDesc('id')
            ->get();
    }
}
