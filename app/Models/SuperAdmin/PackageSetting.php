<?php

namespace App\Models\SuperAdmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

/**
 * App\Models\PackageSetting
 *
 * @property int $id
 * @property string $status
 * @property int|null $no_of_days
 * @property string|null $modules
 * @property string|null $trial_message
 * @property int|null $notification_before
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $all_packages
 * @method static Builder|PackageSetting newModelQuery()
 * @method static Builder|PackageSetting newQuery()
 * @method static Builder|PackageSetting query()
 * @method static Builder|PackageSetting whereCreatedAt($value)
 * @method static Builder|PackageSetting whereId($value)
 * @method static Builder|PackageSetting whereModules($value)
 * @method static Builder|PackageSetting whereNoOfDays($value)
 * @method static Builder|PackageSetting whereNotificationBefore($value)
 * @method static Builder|PackageSetting whereStatus($value)
 * @method static Builder|PackageSetting whereTrialMessage($value)
 * @method static Builder|PackageSetting whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PackageSetting extends BaseModel
{
    use HasFactory;

    // Append virtual attribute when serialized
    protected $appends = ['all_packages'];

    /**
     * Accessor: Determine if the number of modules qualifies as "all packages".
     *
     * @return bool True if modules count is 20 or more, otherwise false.
     */
    public function getAllPackagesAttribute()
    {
        // Decode the JSON string in 'modules' and count the items
        $modules = json_decode($this->modules, true);

        return count($modules) >= 20;
    }
}
