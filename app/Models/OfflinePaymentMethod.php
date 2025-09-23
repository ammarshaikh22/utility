<?php

namespace App\Models;

use App\Traits\HasCompany;
use App\Traits\HasMaskImage;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * App\Models\OfflinePaymentMethod
 *
 * Represents offline/manual payment methods for a company (e.g., Bank Transfer, Cash).
 *
 * @property int $id
 * @property string $name                   Name of the payment method
 * @property string|null $description       Description or instructions
 * @property string|null $status            Status of the method ('yes' = active, 'no' = inactive)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property int|null $company_id           The company this method belongs to
 * @property-read \App\Models\Company|null $company
 *
 * @property-read string $image_url         Public URL for the payment method image
 * @property-read string $masked_image_url  Masked image URL (for apps or restricted usage)
 * @property-read mixed $icon
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfflinePaymentMethod active()
 *
 * @mixin \Eloquent
 */
class OfflinePaymentMethod extends BaseModel
{
    use HasCompany, HasMaskImage;

    /** @var string Table name */
    protected $table = 'offline_payment_methods';

    /** @var array Casts */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** @var array Append computed attributes */
    protected $appends = ['image_url'];

    /**
     * Scope: Return only active payment methods.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'yes');
    }

    /**
     * Fetch all active payment methods.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function activeMethod()
    {
        return self::where('status', 'yes')->get();
    }

    /**
     * Accessor: Get the public image URL.
     *
     * @return string
     */
    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset_url_local_s3('offline-method/' . $this->image)
            : '-';
    }

    /**
     * Accessor: Get the masked image URL (used in apps).
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function maskedImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image
                ? $this->generateMaskedImageAppUrl('offline-method/' . $this->image)
                : '-'
        );
    }
}
