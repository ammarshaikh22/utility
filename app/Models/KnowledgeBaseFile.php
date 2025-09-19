<?php

namespace App\Models;

use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\KnowledgeBaseFile
 *
 * Represents files that are attached to knowledge base articles.
 * A file can either be uploaded locally or linked externally.
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $knowledge_base_id             // Reference to the knowledge base entry
 * @property string|null $filename              // Original file name
 * @property string|null $hashname              // Hashed/unique stored file name
 * @property string|null $size                  // File size
 * @property string|null $external_link_name    // Label for external link
 * @property string|null $external_link         // External file URL (if not stored locally)
 * @property int|null $added_by                 // User who added the file
 * @property int|null $last_updated_by          // User who last updated the file
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * Accessors (Appended attributes):
 * @property-read mixed $file_url               // Computed file URL (external/local)
 * @property-read mixed $icon                   // File icon (from IconTrait)
 *
 * Query Scopes:
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereKnowledgeBaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class KnowledgeBaseFile extends BaseModel
{
    use HasFactory;
    use IconTrait;

    // Directory path where knowledge base files are stored
    const FILE_PATH = 'knowledgebase';

    // Mass-assignment protection
    protected $fillable = [];          // No fields explicitly fillable
    protected $guarded = ['id'];       // "id" is guarded from mass assignment

    // Automatically appended attributes when model is serialized
    protected $appends = ['file_url', 'icon'];

    /**
     * Accessor: Returns file URL.
     * If an external link exists, it returns that.
     * Otherwise, it generates a local S3 storage path.
     *
     * @return string
     */
    public function getFileUrlAttribute()
    {
        return (!is_null($this->external_link))
            ? $this->external_link
            : asset_url_local_s3(
                KnowledgeBaseFile::FILE_PATH . '/' . $this->knowledge_base_id . '/' . $this->hashname
            );
    }
}
