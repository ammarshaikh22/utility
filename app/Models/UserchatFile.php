<?php

namespace App\Models;

use App\Traits\HasCompany;
use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\UserchatFile
 *
 * Represents a file attached to a chat message.
 *
 * @property int $id                        // File ID
 * @property int $user_id                   // ID of the user who uploaded the file
 * @property int $users_chat_id             // Related chat message ID
 * @property string $filename               // Original file name
 * @property string|null $description       // Optional description of the file
 * @property string|null $google_url        // Optional Google Drive URL
 * @property string|null $hashname          // Stored file hash name
 * @property string|null $size              // File size
 * @property string|null $external_link     // External file link
 * @property string|null $external_link_name // Name for external link
 * @property \Illuminate\Support\Carbon|null $created_at // Creation timestamp
 * @property \Illuminate\Support\Carbon|null $updated_at // Update timestamp
 * @property int|null $company_id           // Company ID if multi-tenant
 * @property-read \App\Models\UserChat $chat // Related chat message
 * @property-read mixed $file_url           // Full URL to access the file
 * @property-read mixed $icon               // File icon
 * @property-read mixed $file               // File path or external link
 * @property-read \App\Models\Company|null $company // Related company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile newModelQuery() // Start a new model query
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile newQuery()      // Start a new query builder
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile query()         // Get query builder for this model
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereCreatedAt($value) // Filter by created_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereDescription($value) // Filter by description
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereExternalLink($value) // Filter by external_link
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereExternalLinkName($value) // Filter by external_link_name
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereFilename($value) // Filter by filename
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereGoogleUrl($value) // Filter by Google URL
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereHashname($value) // Filter by hashname
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereId($value) // Filter by ID
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereSize($value) // Filter by size
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereUpdatedAt($value) // Filter by updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereUserId($value) // Filter by user_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereUsersChatId($value) // Filter by users_chat_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserchatFile whereCompanyId($value) // Filter by company_id
 * @mixin \Eloquent
 */
class UserchatFile extends BaseModel
{
    use IconTrait; // Adds file icon logic
    use HasCompany; // Adds multi-company functionality

    const FILE_PATH = 'message-files'; // Base folder for storing uploaded chat files

    protected $appends = ['file_url', 'icon', 'file']; // Auto-include computed attributes when serializing
    protected $table = 'users_chat_files'; // Specify custom table name

    /**
     * Get the full URL for the file.
     * If an external link exists, returns it (adds HTTP check), 
     * otherwise generates URL using local/S3 helper.
     */
    public function getFileUrlAttribute()
    {
        if ($this->external_link) {
            return str($this->external_link)->contains('http') 
                ? $this->external_link 
                : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(UserchatFile::FILE_PATH . '/' . $this->hashname);
    }

    /**
     * Get the file path or external link.
     * Returns external_link if available, otherwise the stored file path.
     */
    public function getFileAttribute()
    {
        return $this->external_link ?: (UserchatFile::FILE_PATH . '/' . $this->hashname);
    }

    /**
     * Define the relationship to the chat message this file belongs to.
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(UserChat::class, 'users_chat_id');
    }
}
