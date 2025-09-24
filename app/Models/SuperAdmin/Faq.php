<?php

namespace App\Models\SuperAdmin;

use App\Models\BaseModel;

/**
 * App\Models\SuperAdmin\Faq
 *
 * Represents a frequently asked question (FAQ) in the system.
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $faq_category_id
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read FaqCategory $category
 * @property-read \Illuminate\Database\Eloquent\Collection|FaqFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $image_url
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Faq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Faq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Faq query()
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereFaqCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereImage($value)
 * @mixin \Eloquent
 */
class Faq extends BaseModel
{
    // Append computed attributes to model output (like JSON)
    public $appends = ['image_url'];

    /**
     * Get the full URL for the FAQ image.
     * If no image is set, a default placeholder image is returned.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        return ($this->image)
            ? asset_url('faq-files/' . $this->id . '/' . $this->image) // Custom image URL
            : asset('saas/img/svg/mock-2.svg'); // Default placeholder image
    }

    /**
     * Define the relationship with the FAQ category.
     * Each FAQ belongs to a single category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }

    /**
     * Define the relationship with FAQ files.
     * Each FAQ can have multiple associated files.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany(FaqFile::class, 'faq_id');
    }
}
