<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\MessageSetting
 *
 * This model represents the messaging-related settings for the application.
 * It defines configuration for client communication permissions and notifications.
 * Each record may optionally belong to a company (via the HasCompany trait).
 *
 * @property int $id Unique identifier for the message setting
 * @property string $allow_client_admin Whether clients with admin role are allowed to send messages (e.g., 'yes'/'no')
 * @property string $allow_client_employee Whether client employees are allowed to send messages (e.g., 'yes'/'no')
 * @property string $restrict_client Restrict message access for certain clients (e.g., 'yes'/'no')
 * @property int $send_sound_notification Whether sound notifications are enabled (1 = yes, 0 = no)
 * @property int|null $company_id Associated company ID if applicable
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when the record was created
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when the record was last updated
 *
 * @property-read \App\Models\Company|null $company The related company (via HasCompany trait)
 * @property-read mixed $icon Accessor for an icon attribute (if implemented in BaseModel or elsewhere)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereAllowClientAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereAllowClientEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereRestrictClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereSendSoundNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageSetting whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class MessageSetting extends BaseModel
{
    use HasCompany;

    // No additional attributes defined, using defaults from BaseModel
}
