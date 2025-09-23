<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Leave
 *
 * Represents an employee leave request/record.
 * Stores details about type, duration, approval, and attachments (files).
 *
 * Core properties:
 * - `user_id` → User who applied for the leave.
 * - `leave_type_id` → Type of leave (sick, casual, etc.).
 * - `leave_date` → The date of the leave.
 * - `status` → Pending, Approved, Rejected, etc.
 * - `approved_by` / `approved_at` → Manager who approved and timestamp.
 * - `files` → Related uploaded files/documents.
 *
 * Relationships:
 * - Belongs to `User` (employee).
 * - Belongs to `User` (approvedBy, manager).
 * - Belongs to `LeaveType`.
 * - Has many `LeaveFile`.
 *
 * @property int $id
 * @property int $user_id
 * @property int $leave_type_id
 * @property int $count
 * @property int $halfday
 * @property string $duration
 * @property \Illuminate\Support\Carbon $leave_date
 * @property string $reason
 * @property string $status
 * @property string|null $reject_reason
 * @property int $paid
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $half_day_type
 * @property string|null $event_id
 * @property string|null $unique_id
 * @property string|null $manager_status_permission
 * @property string|null $approve_reason
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\User $user
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\LeaveType $type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LeaveFile> $files
 * @property-read int|null $files_count
 *
 * @method static \Database\Factories\LeaveFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave query()
 * @mixin \Eloquent
 */
class Leave extends BaseModel
{
    use HasFactory, HasCompany;

    /**
     * Casts for date fields to Carbon instances.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'leave_date' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Protects ID from mass-assignment.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * Appends computed attributes to model JSON.
     *
     * @var array<int, string>
     */
    protected $appends = ['date'];

    /**
     * Accessor: Returns leave_date as a plain date string (Y-m-d).
     *
     * @return string
     */
    public function getDateAttribute()
    {
        return $this->leave_date->toDateString();
    }

    /**
     * Relationship: Leave belongs to a user (employee).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')
            ->withoutGlobalScope(ActiveScope::class)
            ->withOut('clientDetails');
    }

    /**
     * Relationship: Leave was approved by a manager (user).
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')
            ->withoutGlobalScope(ActiveScope::class)
            ->withOut('clientDetails', 'role');
    }

    /**
     * Relationship: Leave belongs to a leave type (sick, casual, etc.).
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id')->withTrashed();
    }

    /**
     * Accessor: Returns total leaves taken by user within defined period.
     *
     * Combines full-day and half-day leaves.
     */
    public function getLeavesTakenCountAttribute()
    {
        $userId = $this->user_id;
        $setting = company();
        $user = User::withoutGlobalScope(ActiveScope::class)
            ->withOut('clientDetails', 'role')
            ->findOrFail($userId);

        $currentYearJoiningDate = Carbon::parse(
            $user->employee[0]->joining_date->format((now(company()->timezone)->year) . '-m-d')
        );

        if ($currentYearJoiningDate->isFuture()) {
            $currentYearJoiningDate->subYear();
        }

        $leaveFrom = $currentYearJoiningDate->copy()->toDateString();
        $leaveTo = $currentYearJoiningDate->copy()->addYear()->toDateString();

        // Adjust leave cycle if not based on joining date
        if ($setting->leaves_start_from !== 'joining_date') {
            $leaveStartYear = Carbon::parse(now()->format((now(company()->timezone)->year) . '-' . company()->year_starts_from . '-01'));
            if ($leaveStartYear->isFuture()) {
                $leaveStartYear = $leaveStartYear->subYear();
            }
            $leaveFrom = $leaveStartYear->copy()->toDateString();
            $leaveTo = $leaveStartYear->copy()->addYear()->toDateString();
        }

        $fullDay = Leave::where('user_id', $userId)
            ->whereBetween('leave_date', [$leaveFrom, $leaveTo])
            ->where('status', 'approved')
            ->where('duration', '<>', 'half day')
            ->count();

        $halfDay = Leave::where('user_id', $userId)
            ->whereBetween('leave_date', [$leaveFrom, $leaveTo])
            ->where('status', 'approved')
            ->where('duration', 'half day')
            ->count();

        return ($fullDay + ($halfDay / 2));
    }

    /**
     * Static helper: Count leaves taken by a user for a specific year.
     *
     * @param \App\Models\User|int $user
     * @param int|null $year
     * @return float|int
     */
    public static function byUserCount($user, $year = null)
    {
        $setting = company();

        if (!$user instanceof User) {
            $user = User::withoutGlobalScope(ActiveScope::class)
                ->withOut('clientDetails', 'role')
                ->findOrFail($user);
        }

        $leaveFrom = is_null($year)
            ? Carbon::createFromFormat('d-m-Y', '01-'.company()->year_starts_from.'-'.now(company()->timezone)->year)
                ->startOfMonth()->toDateString()
            : Carbon::createFromFormat('d-m-Y', '01-'.company()->year_starts_from.'-'.$year)
                ->startOfMonth()->toDateString();

        $leaveTo = Carbon::parse($leaveFrom)->addYear()->subDay()->toDateString();

        // Adjust for joining-date based leave cycle
        if ($setting->leaves_start_from == 'joining_date' && isset($user->employee[0])) {
            $currentYearJoiningDate = Carbon::parse(
                $user->employee[0]->joining_date->format((now(company()->timezone)->year) . '-m-d')
            );

            if ($currentYearJoiningDate->isFuture()) {
                $currentYearJoiningDate->subYear();
            }

            $leaveFrom = $currentYearJoiningDate->copy()->toDateString();
            $leaveTo = $currentYearJoiningDate->copy()->addYear()->toDateString();
        }

        $fullDay = Leave::where('user_id', $user->id)
            ->whereBetween('leave_date', [$leaveFrom, $leaveTo])
            ->where('status', 'approved')
            ->where('duration', '<>', 'half day')
            ->get();

        $halfDay = Leave::where('user_id', $user->id)
            ->whereBetween('leave_date', [$leaveFrom, $leaveTo])
            ->where('status', 'approved')
            ->where('duration', 'half day')
            ->get();

        return (count($fullDay) + (count($halfDay) / 2));
    }

    /**
     * Relationship: Leave has many related files (attachments).
     */
    public function files(): HasMany
    {
        return $this->hasMany(LeaveFile::class, 'leave_id')->orderByDesc('id');
    }
}
