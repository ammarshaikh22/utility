<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Class Holiday
 *
 * Represents holidays in the company calendar.
 * Each holiday may apply to specific departments, designations, or employment types.
 *
 * @package App\Models
 * @property int $id Primary key
 * @property \Illuminate\Support\Carbon $date Date of the holiday
 * @property string|null $occassion Description or reason for the holiday
 * @property \Illuminate\Support\Carbon|null $created_at Record creation timestamp
 * @property \Illuminate\Support\Carbon|null $updated_at Record update timestamp
 * @property int|null $added_by User ID who added the holiday
 * @property int|null $last_updated_by User ID who last updated the holiday
 * @property string|null $event_id Optional external calendar event ID
 * @property int|null $company_id Company reference (for multi-tenancy)
 *
 * @property-read \App\Models\User|null $addedBy Relationship to user who added it
 * @property-read \App\Models\Company|null $company Related company
 * @property-read mixed $icon Accessor for holiday icon
 * @property-read \App\Models\Holiday|null $hdate
 * @property-read \App\Models\Leave|null $ldate
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday query()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereOccassion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereCompanyId($value)
 *
 * @mixin \Eloquent
 */
class Holiday extends BaseModel
{
    use HasCompany; // Adds multi-company support

    // Weekday constants for reference
    const SUNDAY = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = ['date', 'occassion'];

    /**
     * Attributes that cannot be mass-assigned.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * Cast attributes to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * Get holidays between two dates.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  int|null $userId
     * @return \Illuminate\Support\Collection
     */
    public static function getHolidayByDates($startDate, $endDate, $userId = null)
    {
        $holiday = Holiday::select(DB::raw('DATE_FORMAT(date, "%Y-%m-%d") as holiday_date'), 'occassion')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate);

        // If no user ID, return all holidays
        if (is_null($userId)) {
            return $holiday->get();
        }

        // Otherwise, filter holidays relevant to the user’s department, designation, and employment type
        $user = User::find($userId);

        if ($user) {
            $holiday = $holiday->where(function ($query) use ($user) {
                $query->where(function ($subquery) use ($user) {
                    // Department filter
                    $subquery->where(function ($q) use ($user) {
                        $q->where('department_id_json', 'like', '%"' . $user->employeeDetail->department_id . '"%')
                            ->orWhereNull('department_id_json');
                    });
                    // Designation filter
                    $subquery->where(function ($q) use ($user) {
                        $q->where('designation_id_json', 'like', '%"' . $user->employeeDetail->designation_id . '"%')
                            ->orWhereNull('designation_id_json');
                    });
                    // Employment type filter
                    $subquery->where(function ($q) use ($user) {
                        $q->where('employment_type_json', 'like', '%"' . $user->employeeDetail->employment_type . '"%')
                            ->orWhereNull('employment_type_json');
                    });
                });
            });
        }

        return $holiday->get();
    }

    /**
     * Check if a specific date is a holiday.
     *
     * @param  string  $date
     * @return Holiday|null
     */
    public static function checkHolidayByDate($date)
    {
        return Holiday::Where('date', $date)->first();
    }

    /**
     * Relationship: User who added the holiday.
     *
     * @return BelongsTo
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Map weekdays to their localized names.
     *
     * @param  string  $format
     * @return array<int, string>
     */
    public static function weekMap($format = 'l')
    {
        return [
            Holiday::MONDAY => now()->startOfWeek(1)->translatedFormat($format),
            Holiday::TUESDAY => now()->startOfWeek(2)->translatedFormat($format),
            Holiday::WEDNESDAY => now()->startOfWeek(3)->translatedFormat($format),
            Holiday::THURSDAY => now()->startOfWeek(4)->translatedFormat($format),
            Holiday::FRIDAY => now()->startOfWeek(5)->translatedFormat($format),
            Holiday::SATURDAY => now()->startOfWeek(6)->translatedFormat($format),
            Holiday::SUNDAY => now()->startOfWeek(7)->translatedFormat($format),
        ];
    }

    /**
     * Get designation names by IDs.
     *
     * @param  array<int> $ids
     * @return array<string>|null
     */
    public static function designation($ids)
    {
        $designation = null;

        if ($ids != null) {
            $designation = Designation::whereIn('id', $ids)->pluck('name')->toArray();
        }

        return $designation;
    }

    /**
     * Get department names by IDs.
     *
     * @param  array<int> $ids
     * @return array<string>|null
     */
    public static function department($ids)
    {
        $department = null;

        if ($ids != null) {
            $department = Team::whereIn('id', $ids)->pluck('team_name')->toArray();
        }

        return $department;
    }

    /**
     * Relationship: Employees related to the holiday.
     */
    public function employee()
    {
        return $this->hasMany(EmployeeDetails::class, 'user_id');
    }

    /**
     * Relationship: Employee details related to the holiday.
     */
    public function employeeDetails()
    {
        return $this->hasOne(EmployeeDetails::class, 'user_id');
    }
}
