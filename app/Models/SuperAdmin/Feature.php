<?php

namespace App\Models\SuperAdmin;

use App\Models\BaseModel;
use App\Models\LanguageSetting;

/**
 * App\Models\SuperAdmin\Feature
 *
 * Represents a feature displayed on the front-end or in the app.
 * Can have images, icons, and be linked to a specific language.
 *
 * @property int $id
 * @property int|null $language_setting_id
 * @property string $title
 * @property string|null $description
 * @property string|null $image
 * @property string|null $icon
 * @property string $type
 * @property int|null $front_feature_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $image_url
 * @property-read LanguageSetting|null $language
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature query()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereFrontFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereLanguageSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Feature extends BaseModel
{
    // Automatically append the computed attribute 'image_url' when converting to array or JSON
    protected $appends = ['image_url'];

    /**
     * Relationship: Feature belongs to a LanguageSetting (optional)
     */
    public function language()
    {
        return $this->belongsTo(LanguageSetting::class, 'language_setting_id');
    }

    /**
     * Compute the URL of the feature image based on type and title
     *
     * Logic:
     * - If type is 'image' and no image is provided, use default mock images based on title
     * - If type is 'apps', use either stored image or predefined app SVGs (like Onesignal, Paypal, Slack, Pusher)
     * - Otherwise, fallback to a default image
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        // Default images for 'image' type
        if ($this->type == 'image' && is_null($this->image)) {
            if ($this->title == 'Meet Your Business Needs') {
                return asset('saas/img/svg/mock-banner.svg');
            }
            if ($this->title == 'Analyse Your Workflow') {
                return asset('saas/img/svg/mock-2.svg');
            }
            if ($this->title == 'Manage your support tickets efficiently') {
                return asset('saas/img/svg/mock-1.svg');
            }
        }

        // Images for 'apps' type features
        if ($this->type == 'apps') {
            if (!is_null($this->image)) {
                return asset_url_local_s3('front/feature/' . $this->image);
            }

            switch (strtolower($this->title)) {
                case 'onesignal':
                    return asset('saas/img/pages/onesignal.svg');
                case 'paypal':
                    return asset('saas/img/pages/paypal.svg');
                case 'slack':
                    return asset('saas/img/pages/slack-new-logo.svg');
                case 'pusher':
                    return asset('saas/img/pages/pusher.svg');
            }

            // Fallback app image based on ID
            return asset('saas/img/pages/app-' . (($this->id) % 6) . '.png');
        }

        // Default fallback image
        return ($this->image) ? asset_url_local_s3('front/feature/' . $this->image) : asset('front/img/tools.png');
    }
}
