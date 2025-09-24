<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use App\Scopes\CompanyScope;
use App\Models\OfflinePaymentMethod;
use App\Models\BaseModel;
use App\Observers\SuperAdmin\OfflineInvoiceObserver;

/**
 * App\Models\SuperAdmin\OfflineInvoice
 *
 * @property int $id
 * @property int $company_id
 * @property int $package_id
 * @property string|null $package_type
 * @property int|null $offline_method_id
 * @property string|null $transaction_id
 * @property string $amount
 * @property Carbon $pay_date
 * @property Carbon|null $next_pay_date
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read OfflinePaymentMethod|null $offlinePaymentMethod
 * @property-read OfflinePlanChange|null $offlinePlanChangeRequest
 * @property-read Package $package
 * @method static Builder|OfflineInvoice newModelQuery()
 * @method static Builder|OfflineInvoice newQuery()
 * @method static Builder|OfflineInvoice query()
 * @method static Builder|OfflineInvoice whereAmount($value)
 * @method static Builder|OfflineInvoice whereCompanyId($value)
 * @method static Builder|OfflineInvoice whereCreatedAt($value)
 * @method static Builder|OfflineInvoice whereId($value)
 * @method static Builder|OfflineInvoice whereNextPayDate($value)
 * @method static Builder|OfflineInvoice whereOfflineMethodId($value)
 * @method static Builder|OfflineInvoice wherePackageId($value)
 * @method static Builder|OfflineInvoice wherePackageType($value)
 * @method static Builder|OfflineInvoice wherePayDate($value)
 * @method static Builder|OfflineInvoice whereStatus($value)
 * @method static Builder|OfflineInvoice whereTransactionId($value)
 * @method static Builder|OfflineInvoice whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OfflineInvoice extends BaseModel
{
    // Folder path for storing offline invoice files
    const FILE_PATH = 'offline-invoice';

    // Dates to be automatically cast to Carbon instances
    protected $dates = [
        'pay_date',
        'next_pay_date'
    ];

    // Additional casting for datetime fields
    protected $casts = [
        'pay_date' => 'datetime',
        'next_pay_date' => 'datetime',
    ];

    /**
     * Boot method to register model events and global scopes.
     */
    protected static function boot()
    {
        parent::boot();

        // Observe model events (e.g., created, updated)
        static::observe(OfflineInvoiceObserver::class);

        // Apply a global scope for company filtering
        static::addGlobalScope(new CompanyScope);
    }

    /**
     * Relationship to the company associated with this invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id')->withoutGlobalScopes(['active']);
    }

    /**
     * Relationship to the package associated with this invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * Relationship to the offline payment method used for this invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function offlinePaymentMethod()
    {
        return $this->belongsTo(OfflinePaymentMethod::class, 'offline_method_id')
                    ->whereNull('company_id');
    }

    /**
     * Relationship to the related offline plan change request (if any).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function offlinePlanChangeRequest()
    {
        return $this->hasOne(OfflinePlanChange::class, 'invoice_id');
    }
}
