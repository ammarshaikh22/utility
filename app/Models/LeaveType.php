<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\LeaveType
 *
 * Represents the different categories/types of leaves (e.g. Sick Leave, Annual Leave, Maternity Leave).
 * Defines rules such as paid/unpaid, monthly limits, probation restrictions, gender/role restrictions, etc.
 *
 * @property int $id
 * @property string $type_name            // Name of the leave type (e.g., Annual Leave)
 * @property string $color                // UI color code for the leave badge
 * @property int $no_of_leaves            // Allowed number of leaves for this type
 * @property int $monthly_limit           // Max number of leaves per month
 * @property int $paid                    // 1 = Paid leave, 0 = Unpaid leave
 * @property int|null $company_id
 * @property int|null $effective_after    // When leave becomes effective after joining (days/months)
 * @property string|null $effective_type  // "days" or "months"
 * @property string|null $unused_leave    // Handling of unused leaves (carry forward, lapse, etc.)
 * @property int $encashed                // Whether unused leave can be encashed
 * @property int $allowed_probation       // Whether leave is allowed during probation
 * @property int $allowed_notice          // Whether leave is allowed during notice period
 * @property string|null $gender          // Restrict leave to certain genders
 * @property string|null $marital_status  // Restrict leave based on marital status
 * @property string|null $department      // Restrict leave based on department
 * @property string|null $designation     // Restrict leave based on designation
 * @property string|null $role            // Restrict leave based on user role
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Leave[] $leaves
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Leave[] $leavesCount
 * @property-read int|null $leaves_count
 * @property-read \App\Models\Company|null $company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereTypeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereNoOfLeaves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereMonthlyLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType wherePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereEffectiveAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereEffectiveType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereEncashed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereAllowedProbation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereAllowedNotice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereDesignation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereUnusedLeave($value)
 * @mixin \Eloquent
 */
class LeaveType extends BaseModel
{
    use HasCompany, SoftDeletes;

    /**
     * Casts certain attributes into appropriate data types.
     */
    protected $casts = [
        'monthly_limit' => 'float',
    ];

    /**
     * One LeaveType can have many Leave records.
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'leave_type_id');
    }

    /**
     * Relationship to count number of leaves taken by type.
     * Includes half-day handling.
     */
    public function leavesCount(): HasOne
    {
        return $this->hasOne(Leave::class, 'leave_type_id')
            ->selectRaw('leave_type_id, count(*) as count, SUM(if(duration="half day", 1, 0)) AS halfday')
            ->groupBy('leave_type_id');
    }

    /**
     * Fetch leave types available for a given user, optionally filtered by type and status.
     * Considers:
     * - Company leave policy (joining date or fiscal year start)
     * - Employee details like probation, notice period, department, role, etc.
     */
    public static function byUser($user, $leaveTypeId = null, $status = ['approved'], $leaveDate = null)
    {
        if (!is_null($leaveDate)) {
            $leaveDate = Carbon::createFromFormat(company()->date_format, $leaveDate);
        } else {
            $leaveDate = Carbon::createFromFormat(
                'd-m-Y',
                '01-' . company()->year_starts_from . '-' . now(company()->timezone)->year
            )->startOfMonth();
        }

        if (!$user instanceof User) {
            $user = User::withoutGlobalScope(ActiveScope::class)
                ->withOut('clientDetails', 'role')
                ->findOrFail($user);
        }

        $setting = company();

        if (isset($user->employee[0])) {
            if ($setting->leaves_start_from == 'joining_date') {
                // Leaves calculated based on employee joining date
                $currentYearJoiningDate = Carbon::parse(
                    $user->employee[0]->joining_date->format((now(company()->timezone)->year) . '-m-d')
                );

                if ($currentYearJoiningDate->isFuture()) {
                    $currentYearJoiningDate->subYear();
                }

                $leaveTypes = LeaveType::with(['leavesCount' => function ($q) use ($user, $currentYearJoiningDate, $status) {
                    $q->where('leaves.user_id', $user->id);
                    $q->whereBetween('leaves.leave_date', [
                        $currentYearJoiningDate->copy()->toDateString(),
                        $currentYearJoiningDate->copy()->addYear()->toDateString()
                    ]);
                    $q->whereIn('leaves.status', $status);
                }])
                ->select('leave_types.*', 'employee_details.notice_period_start_date', 'employee_details.probation_end_date',
                    'employee_details.department_id as employee_department', 'employee_details.designation_id as employee_designation',
                    'employee_details.marital_status as maritalStatus', 'users.gender as usergender', 'employee_details.joining_date')
                ->join('employee_leave_quotas', 'employee_leave_quotas.leave_type_id', 'leave_types.id')
                ->join('users', 'users.id', 'employee_leave_quotas.user_id')
                ->join('employee_details', 'employee_details.user_id', 'users.id')
                ->where('users.id', $user->id);

                if (!is_null($leaveTypeId)) {
                    $leaveTypes = $leaveTypes->where('leave_types.id', $leaveTypeId);
                }

                return $leaveTypes->get();

            } else {
                // Leaves calculated based on fiscal year start date
                $leaveTypes = LeaveType::with(['leavesCount' => function ($q) use ($user, $status, $leaveDate) {
                    $q->where('leaves.user_id', $user->id);
                    $q->whereBetween('leaves.leave_date', [
                        $leaveDate->copy()->toDateString(),
                        $leaveDate->copy()->addYear()->toDateString()
                    ]);
                    $q->whereIn('leaves.status', $status);
                }])
                ->select('leave_types.*', 'employee_details.notice_period_start_date', 'employee_details.probation_end_date',
                    'employee_details.department_id as employee_department', 'employee_details.designation_id as employee_designation',
                    'employee_details.marital_status as maritalStatus', 'users.gender as usergender', 'employee_details.joining_date')
                ->join('employee_leave_quotas', 'employee_leave_quotas.leave_type_id', 'leave_types.id')
                ->join('users', 'users.id', 'employee_leave_quotas.user_id')
                ->join('employee_details', 'employee_details.user_id', 'users.id')
                ->where('users.id', $user->id);
            }

            if (!is_null($leaveTypeId)) {
                $leaveTypes = $leaveTypes->where('leave_types.id', $leaveTypeId);
            }

            return $leaveTypes->get();
        }

        return collect();
    }

    /**
     * Checks whether a given leave type is applicable to a specific user.
     * Validates:
     * - Probation period
     * - Notice period
     * - Gender, marital status, department, designation
     * - Role match
     * - Effective date (based on joining date + effective_after)
     */
    public function leaveTypeCondition(LeaveType $leave, User $user)
    {
        $currentDate = now();

        if (!$user->employee) {
            return false;
        }

        $userRole = $user->roles->pluck('id')->toArray();
        $leaveRole = $leave->role;

        if (!is_null($leave->effective_type) && !is_null($leave->effective_after)) {
            $effectiveDate = $leave->effective_type == 'days'
                ? $user->employeeDetail->joining_date->addDays($leave->effective_after)
                : $user->employeeDetail->joining_date->addMonths($leave->effective_after);
        }

        $probation = Carbon::parse($leave->probation_end_date);
        $noticePeriod = Carbon::parse($leave->notice_period_start_date);

        if (
            (
                is_null($leave->probation_end_date)
                || ($leave->allowed_probation == 0 && $probation->lt($currentDate))
                || $leave->allowed_probation == 1
            )
            && (
                is_null($leave->notice_period_start_date)
                || ($leave->allowed_notice == 0 && $noticePeriod->gt($currentDate))
                || $leave->allowed_notice == 1
            )
            && (
                !is_null($leave->gender)
                && in_array($user->gender, json_decode($leave->gender))
            )
            && (
                !is_null($leave->marital_status)
                && in_array($user->employeeDetail->marital_status?->value, json_decode($leave->marital_status))
            )
            && (
                !is_null($leave->department)
                && in_array($user->employeeDetail->department?->id, json_decode($leave->department))
            )
            && (
                !is_null($leave->designation)
                && in_array($user->employeeDetail->designation?->id, json_decode($leave->designation))
            )
            && (
                !is_null($leave->role)
                && !empty(array_intersect($userRole, json_decode($leaveRole)))
            )
            && (
                is_null($leave->effective_after)
                || $currentDate->gt($effectiveDate)
            )
        ) {
            return true;
        }

        return false;
    }
}
