<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\PurposeConsent
 *
 * Represents a purpose for which consent is taken. 
 * Can be linked to leads or users.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\PurposeConsentLead|null $lead
 * @property-read \App\Models\PurposeConsentUser|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurposeConsent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PurposeConsent extends BaseModel
{
    // Define the table associated with this model
    protected $table = 'purpose_consent';

    // Fields that can be mass-assigned
    protected $fillable = ['name', 'description'];

    /**
     * Get the lead record associated with this consent.
     *
     * @return HasOne
     */
    public function lead(): HasOne
    {
        return $this->hasOne(PurposeConsentDeal::class, 'purpose_consent_id', 'id');
    }

    /**
     * Get the user record associated with this consent.
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(PurposeConsentUser::class, 'purpose_consent_id', 'id');
    }
}
