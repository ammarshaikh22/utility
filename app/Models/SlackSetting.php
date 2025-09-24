<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\SlackSetting
 *
 * Represents Slack configuration settings for a company.
 *
 * @property int $id
 * @property string|null $slack_webhook
 * @property string|null $slack_logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $status
 * @property int|null $company_id
 * @property-read mixed $icon
 * @property-read mixed $slack_logo_url
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereSlackLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereSlackWebhook($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereCompanyId($value)
 * @mixin \Eloquent
 */
class SlackSetting extends BaseModel
{
    // Include HasCompany trait to associate Slack settings with a company
    use HasCompany;

    // Append custom attributes to model output
    protected $appends = ['slack_logo_url'];

    // Accessor for Slack logo URL
    public function getSlackLogoUrlAttribute()
    {
        if (is_null($this->slack_logo)) {
            return $this->company->logo_url; // Fallback to company logo if Slack logo is null
        }

        return asset_url_local_s3('slack-logo/' . $this->slack_logo);
    }

    // Helper to get the Slack setting
    public static function setting()
    {
        return slack_setting();
    }
}
