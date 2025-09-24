<?php

namespace App\Models\SuperAdmin;

use App\Models\LanguageSetting;
use App\Models\BaseModel;

/**
 * App\Models\SuperAdmin\FrontFeature
 *
 * Represents a front-end feature section, which can have multiple Feature items.
 * Optionally linked to a language setting for multilingual support.
 *
 * @property int $id
 * @property int|null $language_setting_id
 * @property string|null $title
 * @property string|null $description
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Feature[] $features
 * @property-read int|null $features_count
 * @property-read LanguageSetting|null $language
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFeature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFeature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFeature query()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFeature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFeature whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFeature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFeature whereLanguageSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFeature whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFeature whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFeature whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FrontFeature extends BaseModel
{
    /**
     * Defines a one-to-many relationship with the Feature model.
     * Each FrontFeature can have multiple Features linked by 'front_feature_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function features()
    {
        return $this->hasMany(Feature::class, 'front_feature_id');
    }

    /**
     * Defines the relationship to LanguageSetting.
     * Allows fetching the language associated with this front feature.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(LanguageSetting::class, 'language_setting_id');
    }
}
