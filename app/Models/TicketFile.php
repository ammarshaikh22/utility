<?php

namespace App\Models;

use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TicketFile
 *
 * Represents a file attached to a ticket reply.
 *
 * @property int $id
 * @property int $user_id                  // The user who uploaded the file
 * @property int $ticket_reply_id          // The ticket reply this file is attached to
 * @property string $filename              // Original filename
 * @property string|null $description      // Optional description for the file
 * @property string|null $google_url       // Optional Google Drive link
 * @property string|null $hashname         // Internal hashed filename
 * @property string|null $size             // File size
 * @property string|null $dropbox_link     // Optional Dropbox link
 * @property string|null $external_link    // Optional external file URL
 * @property string|null $external_link_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * Relations:
 * @property-read \App\Models\TicketReply $reply   // The ticket reply this file belongs to
 *
 * Appended attributes:
 * @property-read mixed $file_url   // Full URL to access the file
 * @property-read mixed $icon       // Icon based on file type
 * @property-read mixed $file       // Path or external link
 *
 * @mixin \Eloquent
 */
class TicketFile extends BaseModel
{
    use IconTrait;

    // Base path where ticket files are stored
    const FILE_PATH = 'ticket-files';

    // Attributes to automatically append to model's array and JSON
    protected $appends = ['file_url', 'icon', 'file'];

    /**
     * Get full file URL for this ticket file.
     */
    public function getFileUrlAttribute()
    {
        if($this->external_link){
            return str($this->external_link)->contains('http') 
                ? $this->external_link 
                : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(TicketFile::FILE_PATH . '/' . $this->ticket_reply_id . '/' . $this->hashname);
    }

    /**
     * Get file path or external link.
     */
    public function getFileAttribute()
    {
        return $this->external_link ?: (TicketFile::FILE_PATH . '/' . $this->ticket_reply_id . '/' . $this->hashname);
    }

    /**
     * Relation: Ticket reply this file belongs to.
     */
    public function reply(): BelongsTo
    {
        return $this->belongsTo(TicketReply::class);
    }
}
