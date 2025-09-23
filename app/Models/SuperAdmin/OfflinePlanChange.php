<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Scopes\CompanyScope;
use App\Models\OfflinePaymentMethod;

/**
 * App\Models\SuperAdmin\OfflinePlanChange
 *
 * @property int $id
 * @property int $company_id
 * @property int $package_id
 * @property string $package_type
 * @property int $invoice_id
 * @property int $offline_method_id
 * @property string|null $file_name
 * @property string $status
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read mixed $file
 * @property-read OfflinePaymentMethod $offlineMethod
 * @property-read Package $package
 * @method static Builder|OfflinePlanChange newModelQuery()
 * @method static Builder|OfflinePlanChange newQuery()
 * @method static Builder|OfflinePlanChange query()
 * @method static Builder|OfflinePlanChange whereCompanyId($value)
 * @method static Builder|OfflinePlanChange whereCreatedAt($value)
 * @method static Builder|OfflinePlanChange whereDescription($value)
 * @method static Builder|OfflinePlanChange whereFileName($value)
 * @method static Builder|OfflinePlanChange whereId($value)
 * @method static Builder|OfflinePlanChange whereInvoiceId($value)
 * @method static Builder|OfflinePlanChange whereOfflineMethodId($value)
 * @method static Builder|OfflinePlanChange wherePackageId($value)
 * @method static Builder|OfflinePlanChange wherePackageType($value)
 * @method static Builder|OfflinePlanChange whereStatus($value)
 * @method static Builder|OfflinePlanChange whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OfflinePlanChange extends BaseModel
{
    // Trait to handle company-specific functionality
    use HasCompany;

    // Folder path to store offline plan change files
    const FILE_PATH = 'offline-invoice';

    // Automatically cast these fields to Carbon instances
    protected $dates = [
        'pay_date',
        'next_pay_date'
    ];

    // Additional casts
    protected $casts = [
        'pay_date' => 'datetime',
        'next_pay_date' => 'datetime',
    ];

    // Append custom attributes to model's array/JSON form
    protected $appends = ['file'];

    /**
     * Relationship to the company making the request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Relationship to the package requested in the plan change.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * Relationship to the offline payment method used.
     * Ignores the global CompanyScope to allow fetching any offline method.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function offlineMethod()
    {
        return $this->belongsTo(OfflinePaymentMethod::class, 'offline_method_id')
                    ->withoutGlobalScope(CompanyScope::class);
    }

    /**
     * Accessor for the file attribute.
     * Returns the full URL to the uploaded file, or a default image if not set.
     *
     * @return string
     */
    public function getFileAttribute()
    {
        return ($this->file_name) 
            ? asset_url_local_s3(self::FILE_PATH . '/' . $this->file_name) 
            : asset('img/default-profile-3.png');
    }
}
