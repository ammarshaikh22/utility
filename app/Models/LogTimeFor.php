<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * Class LogTimeFor
 *
 * This model represents the `log_time_for` table, which is used to configure
 * settings related to time logging, auto-stop timers, tracker reminders,
 * and reporting. It is also linked with a company (via HasCompany trait).
 *
 * @package App\Models
 *
 * @property int $id                       // Primary key
 * @property string $log_time_for          // Defines what time is logged for (e.g., tasks, projects, etc.)
 * @property string $auto_timer_stop       // Whether to auto-stop timers
 * @property \Illuminate\Support\Carbon|null $created_at // Timestamp when record was created
 * @property \Illuminate\Support\Carbon|null $updated_at // Timestamp when record was last updated
 * @property int $approval_required        // Whether approval is required for logged time
 * @property int|null $company_id          // Associated company ID
 * @property int $tracker_reminder         // Reminder setting for time tracker
 * @property int $timelog_report           // Timelog report flag
 * @property string|null $daily_report_roles // Roles that receive daily reports (stored as JSON/string)
 * @property string|null $time             // Specific time configuration for daily report/tracker
 *
 * @property-read mixed $icon              // Virtual property (from traits or accessors)
 * @property-read \App\Models\Company|null $company // Related company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor query()
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereApprovalRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereAutoTimerStop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereLogTimeFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereDailyReportRoles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereTimelogReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogTimeFor whereTrackerReminder($value)
 * @mixin \Eloquent
 */
class LogTimeFor extends BaseModel
{
    use HasCompany; // Adds company relationship and scoping

    // The attributes that are mass assignable (currently none)
    protected $fillable = [];

    // Attributes that cannot be mass assigned
    protected $guarded = ['id'];

    // Table name for this model
    protected $table = 'log_time_for';
}
