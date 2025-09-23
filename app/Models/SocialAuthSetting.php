<?php

namespace App\Models;

/**
 * App\Models\SocialAuthSetting
 *
 * Handles configuration for social login integrations.
 *
 * @property int $id
 * @property string|null $facebook_client_id
 * @property string|null $facebook_secret_id
 * @property string $facebook_status
 * @property string|null $google_client_id
 * @property string|null $google_secret_id
 * @property string $google_status
 * @property string|null $twitter_client_id
 * @property string|null $twitter_secret_id
 * @property string $twitter_status
 * @property string|null $linkedin_client_id
 * @property string|null $linkedin_secret_id
 * @property string $linkedin_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $social_auth_enable Indicates if at least one social auth is enabled
 * @property-read int $social_auth_enable_count Number of enabled social auth providers
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereFacebookClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereFacebookSecretId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereFacebookStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereGoogleClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereGoogleSecretId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereGoogleStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereLinkedinClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereLinkedinSecretId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereLinkedinStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereTwitterClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereTwitterSecretId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereTwitterStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SocialAuthSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SocialAuthSetting extends BaseModel
{
    // Table used for social auth settings
    protected $table = 'social_auth_settings';

    // Guard the ID field from mass assignment
    protected $guarded = ['id'];

    // Append custom attributes when model is serialized
    protected $appends = ['social_auth_enable', 'social_auth_enable_count'];

    // Encrypt sensitive secret IDs
    protected $casts = [
        'facebook_secret_id' => 'encrypted',
        'google_secret_id' => 'encrypted',
        'linkedin_secret_id' => 'encrypted',
        'twitter_secret_id' => 'encrypted',
    ];

    /**
     * Returns true if at least one social auth provider is enabled
     */
    public function getSocialAuthEnableAttribute()
    {
        return in_array('enable', [
            $this->linkedin_status,
            $this->google_status,
            $this->twitter_status,
            $this->facebook_status
        ]);
    }

    /**
     * Returns the count of social auth providers that are enabled
     */
    public function getSocialAuthEnableCountAttribute()
    {
        $statuses = [
            $this->linkedin_status,
            $this->google_status,
            $this->twitter_status,
            $this->facebook_status
        ];

        return count(array_filter($statuses, function ($status) {
            return $status == 'enable';
        }));
    }
}
