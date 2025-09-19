<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\KnowledgeBaseCategory
 *
 * Represents a category in the knowledge base.
 * Each category can belong to a company and contain multiple knowledge base articles.
 *
 * @property int $id
 * @property string $name
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * Relationships:
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\KnowledgeBase[] $knowledgebase
 * @property-read int|null $knowledgebase_count
 * @property-read \App\Models\Company|null $company
 *
 * Query Scopes:
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KnowledgeBaseCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class KnowledgeBaseCategory extends BaseModel
{
    use HasFactory, HasCompany;

    // Database table associated with this model
    protected $table = 'knowledge_categories';

    /**
     * Relationship: A category can have many knowledge base articles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function knowledgebase(): HasMany
    {
        return $this->hasMany(KnowledgeBase::class, 'category_id');
    }
}
