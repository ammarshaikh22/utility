<?php

namespace App\Models;

use App\Traits\HasMaskImage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCompany;

/**
 * Class InvoicePaymentDetail
 *
 * Represents payment details for invoices (e.g., bank transfer, offline method).
 * Stores company-specific payment instructions and optional image (logo, QR, etc.).
 */
class InvoicePaymentDetail extends BaseModel
{
    // Traits for handling company-specific data and masked images
    use HasCompany, HasMaskImage;

    // Database table name
    protected $table = 'invoice_payment_details';

    // Mass-assignable fields
    protected $fillable = ['title', 'company_id', 'payment_details'];

    // Virtual attributes to append automatically when model is serialized
    protected $appends = ['image_url'];

    /**
     * Relationship: One payment detail can be linked to multiple invoices.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Relationship: Belongs to a company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Accessor: Returns the public/local URL of the payment detail image.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        // If image exists, return asset URL, otherwise return '-'
        return ($this->image) ? asset_url_local_s3('offline-method/' . $this->image) : '-';
    }

    /**
     * Accessor using Laravel's Attribute class:
     * Returns a masked (secured) image URL instead of the raw one.
     *
     * @return Attribute
     */
    public function maskedImageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                // If image exists, generate masked URL via trait; otherwise return '-'
                return ($this->image) ? $this->generateMaskedImageAppUrl('offline-method/' . $this->image) : '-';
            },
        );
    }
}
