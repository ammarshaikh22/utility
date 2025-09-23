<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\VisaDetail
 *
 * Represents visa information for a user.
 *
 * @property int $id                              // Primary key
 * @property int|null $company_id                 // Related company ID
 * @property int|null $user_id                    // User ID to whom this visa belongs
 * @property int|null $country_id                 // Country ID for the visa
 * @property int|null $added_by                    // ID of the user who added this record
 * @property string $visa_number                  // Visa number
 * @property \Illuminate\Support\Carbon $issue_date  // Visa issue date
 * @property \Illuminate\Support\Carbon $expiry_date // Visa expiry date
 * @property string|null $file                    // Visa file name
 * @property string|null $image                   // Optional image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user    // Associated user
 * @property-read \App\Models\Country|null $country // Associated country
 * @property-read mixed $image_url                // Full URL to the visa file
 * @property-read \App\Models\Company|null $company // Associated company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VisaDetail whereVisaNumber($value)
 * @mixin \Eloquent
 */
class VisaDetail extends BaseModel
{
    use HasCompany;

    const FILE_PATH = 'visa'; // Folder where visa files are stored

    protected $appends = ['image_url']; // Append computed attribute to model output

    protected $casts = [
        'issue_date' => 'datetime',  // Cast issue_date to Carbon instance
        'expiry_date' => 'datetime', // Cast expiry_date to Carbon instance
    ];

    /**
     * Get the associated user of this visa.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor to get the full URL of the visa image/file.
     */
    public function getImageUrlAttribute()
    {
        return asset_url_local_s3(VisaDetail::FILE_PATH . '/'  . $this->file);
    }

    /**
     * Get the associated country of the visa.
     */
    public function country(): HasOne
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }
}
