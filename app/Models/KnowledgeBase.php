<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\KnowledgeBase
 *
 * Represents an article or entry in the knowledge base.
 * Each knowledge base item can belong to a category, a company,
 * and may have multiple attached files.
 *
 * @property protected $appends Extra attributes appended to JSON output
 * @property int $id Unique identifier
 * @property string $to Target audience (e.g., 'client', 'employee')
 * @property string $heading Title/heading of the knowledge base article
 * @property int|null $category_id Foreign key to KnowledgeBaseCategory
 * @property string|null $description Content/description of the article
 * @property int $added_by ID of the user who created the article
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when created
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when last updated
 * @property int|null $company_id Foreign key to the company
 *
 * @property-read \App\Models\KnowledgeBaseCategory|null $knowledgebasecategory Category relationship
 * @property-read \App\Models\Company|null $company Related company
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\KnowledgeBaseFile[] $files Related files
 * @property-read int|null $files_count Count of related files
 *
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase query()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereHeading($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBase whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class KnowledgeBase extends BaseModel
{
    use HasFactory, HasCompany;

    /**
     * Directory path for storing knowledge base files.
     */
    const FILE_PATH = 'knowledgebase';

    /**
     * Get the category this knowledge base article belongs to.
     */
    public function knowledgebasecategory(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseCategory::class, 'category_id');
    }

    /**
     * Get all files attached to this knowledge base article.
     * Ordered by most recent first.
     */
    public function files(): HasMany
    {
        return $this->hasMany(KnowledgeBaseFile::class, 'knowledge_base_id')->orderByDesc('id');
    }
}
