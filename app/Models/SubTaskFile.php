<?php

namespace App\Models;

use App\Traits\IconTrait;

/**
 * App\Models\SubTaskFile
 *
 * Represents a file attached to a subtask.
 *
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property int $sub_task_id
 * @property string $filename
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $dropbox_link
 * @property string|null $external_link
 * @property string|null $external_link_name
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read mixed $file
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereSubTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubTaskFile whereUserId($value)
 * @mixin \Eloquent
 */
class SubTaskFile extends BaseModel
{
    use IconTrait;

    // Base directory for storing subtask files
    const FILE_PATH = 'sub-task-files';

    // Automatically append these attributes when serializing
    protected $appends = ['file_url', 'icon', 'file'];

    /**
     * Get the full URL for the file.
     * If external_link exists, returns it (ensures http prefix), 
     * otherwise constructs S3/local storage URL using hashname.
     *
     * @return string
     */
    public function getFileUrlAttribute()
    {
        if ($this->external_link) {
            return str($this->external_link)->contains('http') 
                ? $this->external_link 
                : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(
            SubTaskFile::FILE_PATH . '/' . $this->sub_task_id . '/' . $this->hashname
        );
    }

    /**
     * Returns the relative file path or external link.
     *
     * @return string
     */
    public function getFileAttribute()
    {
        return $this->external_link ?: (SubTaskFile::FILE_PATH . '/' . $this->sub_task_id . '/' . $this->hashname);
    }
}
