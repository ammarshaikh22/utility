<?php

namespace App\Models;

use App\Traits\HasCompany;
use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\FileStorage
 *
 * Represents a file stored in the system along with its metadata such as size, type, and storage location.
 *
 * @property int $id
 * @property string $path                 Directory path of the stored file
 * @property string $filename             Original filename
 * @property string|null $type            File type (MIME type or category)
 * @property int $size                    File size in bytes
 * @property string $storage_location     Storage location (e.g., local, s3)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $file_url         Computed URL for accessing the file
 * @property-read mixed $icon             File type icon representation
 * @property-read mixed $size_format      Human-readable file size format
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage query()
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereStorageLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileStorage whereFilename($value)
 * 
 * @mixin \Eloquent
 */
class FileStorage extends BaseModel
{
    use HasFactory, IconTrait, HasCompany;

    /** @var string The database table associated with the model. */
    protected $table = 'file_storage';

    /** @var array Attributes that should be appended to model's array form. */
    protected $appends = ['file_url', 'icon', 'size_format'];

    /**
     * Get the full file URL from storage.
     *
     * @return string
     */
    public function getFileUrlAttribute()
    {
        return asset_url_local_s3($this->path . '/' . $this->filename);
    }

    /**
     * Get the file size in a human-readable format (e.g., KB, MB, GB).
     *
     * @return string
     */
    public function getSizeFormatAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        if ($bytes > 1) {
            return $bytes . ' bytes';
        }

        if ($bytes == 1) {
            return $bytes . ' byte';
        }

        return '0 bytes';
    }
}
