<?php

namespace App\Models\SuperAdmin;

use App\Models\LanguageSetting;
use App\Models\BaseModel;

/**
 * App\Models\SuperAdmin\FrontFaq
 *
 * Represents a Frequently Asked Question entry for the front-end,
 * optionally linked to a language setting.
 *
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property int|null $language_setting_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read LanguageSetting|null $language
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFaq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFaq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFaq query()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFaq whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFaq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFaq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFaq whereLanguageSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFaq whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontFaq whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FrontFaq extends BaseModel
{
    // Protects the id from mass assignment
    protected $guarded = ['id'];

    /**
     * Define the relationship to LanguageSetting.
     * Allows fetching the language associated with this FAQ.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(LanguageSetting::class, 'language_setting_id');
    }
}
