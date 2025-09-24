<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\LeaveFile
 *
 * Represents a file attached to a leave request.
 * Stores information like filename, size, and path for employee leave-related documents.
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $user_id
 * @property int $leave_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read \App\Models\Leave $leave
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereLeaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveFile whereUserId($value)
 * @mixin \Eloquent
 */
class LeaveFile extends BaseModel
{
    use IconTrait;
    use HasFactory;

    /**
     * Directory path where leave files are stored.
     */
    const FILE_PATH = 'leave-files';

    /**
     * Append these attributes when model is serialized.
     *
     * @var array<int, string>
     */
    protected $appends = ['file_url', 'icon'];

    /**
     * Relationship: Each leave file belongs to one leave request.
     */
    public function leave(): BelongsTo
    {
        return $this->belongsTo(Leave::class);
    }

    /**
     * Accessor: Get the full URL of the stored leave file.
     *
     * @return string
     */
    public function getFileUrlAttribute()
    {
        return asset_url_local_s3(
            LeaveFile::FILE_PATH . '/' . $this->leave_id . '/' . $this->hashname
        );
    }
}
