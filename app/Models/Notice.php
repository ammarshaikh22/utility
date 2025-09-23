<?php

namespace App\Models;

use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\Notice
 *
 * This model represents notices or announcements created within the system.
 * Notices can be targeted to employees, clients, or departments, and may
 * have attached files or records of members who viewed them.
 *
 * @property int $id Unique identifier of the notice
 * @property string $to Target audience (e.g., 'employee', 'client', 'all')
 * @property string $heading Title or heading of the notice
 * @property string|null $description Detailed description of the notice
 * @property int|null $department_id The department this notice belongs to (if any)
 * @property int|null $added_by User ID who created the notice
 * @property int|null $last_updated_by User ID who last updated the notice
 * @property int|null $company_id The company this notice is associated with
 * @property \Illuminate\Support\Carbon|null $created_at Notice creation timestamp
 * @property \Illuminate\Support\Carbon|null $updated_at Notice update timestamp
 *
 * Relationships:
 * @property-read \App\Models\Team|null $department Department the notice belongs to
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NoticeView[] $member Users who have viewed this notice
 * @property-read int|null $member_count Count of users who have viewed this notice
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NoticeBoardUser[] $noticeEmployees Employees assigned to the notice
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NoticeBoardUser[] $noticeClients Clients assigned to the notice
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NoticeFile[] $files Files attached to the notice
 * @property-read mixed $notice_date Human-readable formatted notice date
 *
 * @method static \Database\Factories\NoticeFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notice query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereHeading($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Notice extends BaseModel
{
    use Notifiable, HasFactory, HasCompany;

    /**
     * Append computed attributes when model is converted to array/json.
     */
    protected $appends = ['notice_date'];

    /**
     * Get members (users) who have viewed this notice.
     */
    public function member(): HasMany
    {
        return $this->hasMany(NoticeView::class, 'notice_id');
    }

    /**
     * Get notice recipients who are employees.
     */
    public function noticeEmployees(): HasMany
    {
        return $this->hasMany(NoticeBoardUser::class, 'notice_id')->where('to', 'employee');
    }

    /**
     * Get notice recipients who are clients.
     */
    public function noticeClients(): HasMany
    {
        return $this->hasMany(NoticeBoardUser::class, 'notice_id')->where('to', 'client');
    }

    /**
     * Accessor: Returns formatted notice creation date.
     *
     * @return string
     */
    public function getNoticeDateAttribute()
    {
        if (!is_null($this->created_at)) {
            return Carbon::parse($this->created_at)->format('d F, Y');
        }

        return '';
    }

    /**
     * Get the department this notice belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'department_id', 'id');
    }

    /**
     * Get files attached to the notice, ordered by newest first.
     */
    public function files(): HasMany
    {
        return $this->hasMany(NoticeFile::class, 'notice_id')->orderByDesc('id');
    }
}
