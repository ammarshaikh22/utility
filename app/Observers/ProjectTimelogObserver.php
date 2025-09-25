<?php

namespace App\Observers;

use App\Events\TimelogEvent;
use App\Models\EmployeeDetails;
use App\Models\LogTimeFor;
use App\Models\ProjectMember;
use App\Models\ProjectTimeLog;
use Illuminate\Support\Str;
use App\Traits\EmployeeActivityTrait;

class ProjectTimelogObserver
{
    use EmployeeActivityTrait;

    /**
     * Handle the "saving" event for ProjectTimeLog.
     *
     * Runs before a timelog is saved (create or update).
     * - Tracks who last updated the timelog.
     * - Ensures correct project association and hourly rate.
     * - Calculates earnings based on total time minus breaks.
     * - Fires a TimelogEvent for real-time updates/notifications.
     */
    public function saving(ProjectTimeLog $projectTimeLog)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $projectTimeLog->last_updated_by = user()->id;
        }

        if (!isRunningInConsoleOrSeeding()) {
            $userId = (request()->has('user_id') ? request('user_id') : $projectTimeLog->user_id);
            $projectId = request('project_id');

            // Determine project and member hourly rate
            if ($projectId != '') {
                if ($projectTimeLog->project->public == 1) {
                    $member = EmployeeDetails::where('user_id', $userId)->first();
                } else {
                    $member = ProjectMember::where('user_id', $userId)->where('project_id', $projectId)->first();
                }

                $projectTimeLog->hourly_rate = ($member && !is_null($member->hourly_rate) ? $member->hourly_rate : 0);
                $projectTimeLog->project_id = $projectId;
            } else {
                // Fallback: get project from related task
                $task = $projectTimeLog->task;

                if (!is_null($task) && !is_null($task->project_id)) {
                    $projectId = $task->project_id;
                    $projectTimeLog->project_id = $task->project_id;
                }

                $member = EmployeeDetails::where('user_id', $userId)->first();
                $projectTimeLog->hourly_rate = (!is_null($member->hourly_rate) ? $member->hourly_rate : 0);
            }

            // Calculate earnings
            $minuteRate = $projectTimeLog->hourly_rate / 60;
            $totalMinutes = $projectTimeLog->total_minutes;
            $breakMinutes = $projectTimeLog->breaks()->sum('total_minutes');
            $earning = round(($totalMinutes - $breakMinutes) * $minuteRate, 2);
            /* @phpstan-ignore-line */
            $projectTimeLog->earnings = $earning;

            // Handle duplicate task assignment
            $urlDuplicateTask = Str::contains(url()->previous(), 'duplicate_task');
            if ($urlDuplicateTask && $projectId != '') {
                $projectTimeLog->project_id = $projectTimeLog->task->project_id;
            }

            // Fire event
            event(new TimelogEvent($projectTimeLog));
        }
    }

    /**
     * Handle the "creating" event for ProjectTimeLog.
     *
     * - Sets `added_by` field to current user.
     * - Applies approval settings if required by LogTimeFor configuration.
     * - Associates the timelog with the current company.
     */
    public function creating(ProjectTimeLog $projectTimeLog)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $projectTimeLog->added_by = user()->id;
        }

        if (!isRunningInConsoleOrSeeding()) {
            $timeLogSetting = LogTimeFor::first();

            if ($timeLogSetting->approval_required) {
                $projectTimeLog->approved = 0;
                $projectTimeLog->rejected = 0;
            }
        }

        if (company()) {
            $projectTimeLog->company_id = company()->id;
        }
    }

    /**
     * Handle the "created" event.
     *
     * Logs employee activity when a new timelog is created.
     */
    public function created(ProjectTimeLog $projectTimeLog)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'timelog-created', $projectTimeLog->id, 'timelog');
        }
    }

    /**
     * Handle the "updated" event.
     *
     * Logs employee activity when a timelog is updated.
     */
    public function updated(ProjectTimeLog $projectTimeLog)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'timelog-updated', $projectTimeLog->id, 'timelog');
        }
    }

    /**
     * Handle the "deleted" event.
     *
     * Logs employee activity when a timelog is deleted.
     */
    public function deleted(ProjectTimeLog $projectTimeLog)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'timelog-deleted');
        }
    }
}
