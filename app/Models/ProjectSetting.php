<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\ProjectSetting
 *
 * Represents settings related to project reminders (who to notify, when, and how).
 *
 * @property int $id
 * @property string $send_reminder Whether reminders should be sent (yes/no)
 * @property int $remind_time Time value for reminders (e.g., 5, 10)
 * @property string $remind_type Type of time unit (e.g., minutes, hours, days)
 * @property string|array $remind_to Users who should receive reminders (JSON encoded in DB, array when accessed)
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when created
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when updated
 * @property int|null $company_id Related company ID
 *
 * @property-read \App\Models\Company|null $company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereSendReminder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereRemindTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereRemindType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereRemindTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectSetting whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProjectSetting extends BaseModel
{
    use HasCompany;

    /** Reminder target constants */
    const REMIND_TO_MEMBERS = 'members';
    const REMIND_TO_ADMINS = 'admins';

    /**
     * Accessor: Decode `remind_to` JSON into array when retrieved.
     */
    public function getRemindToAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * Mutator: Encode `remind_to` array into JSON before saving.
     */
    public function setRemindToAttribute($value)
    {
        $this->attributes['remind_to'] = json_encode($value);
    }
}
