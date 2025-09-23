<?php

namespace App\Models;

use App\Traits\IconTrait;

/**
 * App\Models\NoticeFile
 *
 * This model represents files attached to a Notice.
 * A file can either be stored locally (with a path) or referenced via an external link.
 *
 * @property int $id
 * @property int $notice_id        The ID of the related notice
 * @property string|null $filename Original name of the file
 * @property string|null $hashname Hashed file name stored in the system
 * @property string|null $external_link External file link (if not stored locally)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read string $file_url Computed URL for accessing the file (local or external)
 * @property-read string $file     Computed file path or external link
 * @property-read string $icon     File icon provided by the IconTrait
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeFile whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class NoticeFile extends BaseModel
{
    use IconTrait;

    /**
     * Base directory path for storing notice files.
     */
    const FILE_PATH = 'notice-files';

    /**
     * Attributes to append when the model is converted to an array or JSON.
     *
     * @var array<int, string>
     */
    protected $appends = ['file_url', 'icon', 'file'];

    /**
     * Accessor: Get the full URL of the file (either external or local).
     *
     * @return string
     */
    public function getFileUrlAttribute(): string
    {
        if ($this->external_link) {
            return str($this->external_link)->contains('http')
                ? $this->external_link
                : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(
            self::FILE_PATH . '/' . $this->notice_id . '/' . $this->hashname
        );
    }

    /**
     * Accessor: Get the file path or external link.
     *
     * @return string
     */
    public function getFileAttribute(): string
    {
        return $this->external_link
            ?: (self::FILE_PATH . '/' . $this->notice_id . '/' . $this->hashname);
    }
}
