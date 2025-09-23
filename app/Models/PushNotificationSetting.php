<?php

namespace App\Models;

/**
 * App\Models\PushNotificationSetting
 *
 * Represents the settings for push notifications using OneSignal.
 *
 * @property int $id
 * @property string|null $onesignal_app_id
 * @property string|null $onesignal_rest_api_key
 * @property string|null $notification_logo
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read mixed $notification_logo_url
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereNotificationLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereOnesignalAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereOnesignalRestApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PushNotificationSetting extends BaseModel
{
    // Append custom attribute to the model for retrieving logo URL
    protected $appends = ['notification_logo_url'];

    /**
     * Get the full URL for the notification logo.
     * If no logo is set, return a placeholder image with descriptive text.
     *
     * @return string
     */
    public function getNotificationLogoUrlAttribute()
    {
        if (is_null($this->notification_logo)) {
            return 'http://via.placeholder.com/200x150.png?text=' . __('modules.slackSettings.uploadSlackLogo');
        }

        return asset_url_local_s3('notification-logo/' . $this->notification_logo);
    }
}
