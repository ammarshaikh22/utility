<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\QuickBooksSetting
 *
 * Represents QuickBooks integration settings for a company.
 *
 * @property int $id
 * @property int|null $company_id The ID of the associated company.
 * @property string $sandbox_client_id QuickBooks sandbox client ID.
 * @property string $sandbox_client_secret QuickBooks sandbox client secret.
 * @property string $client_id QuickBooks production client ID.
 * @property string $client_secret QuickBooks production client secret.
 * @property string $access_token Access token for QuickBooks API.
 * @property string $refresh_token Refresh token for QuickBooks API.
 * @property string $realmid QuickBooks company ID (realm ID).
 * @property string $sync_type Type of sync with QuickBooks.
 * @property string $environment Environment: 'sandbox' or 'production'.
 * @property int $status Status of the QuickBooks integration.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company Associated company.
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereEnvironment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereRealmid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereSandboxClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereSandboxClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereSyncType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuickBooksSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class QuickBooksSetting extends BaseModel
{
    use HasCompany;

    // Protect the 'id' field from mass assignment
    protected $guarded = ['id'];
}
