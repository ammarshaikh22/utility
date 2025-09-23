<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use App\Models\BaseModel;

/**
 * App\Models\SuperAdmin\MollieInvoice
 *
 * @property int $id
 * @property int $company_id
 * @property int $package_id
 * @property string|null $transaction_id
 * @property string|null $amount
 * @property string|null $package_type
 * @property Carbon|null $pay_date
 * @property Carbon|null $next_pay_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read Package $package
 * @method static Builder|MollieInvoice newModelQuery()
 * @method static Builder|MollieInvoice newQuery()
 * @method static Builder|MollieInvoice query()
 * @method static Builder|MollieInvoice whereAmount($value)
 * @method static Builder|MollieInvoice whereCompanyId($value)
 * @method static Builder|MollieInvoice whereCreatedAt($value)
 * @method static Builder|MollieInvoice whereId($value)
 * @method static Builder|MollieInvoice whereNextPayDate($value)
 * @method static Builder|MollieInvoice wherePackageId($value)
 * @method static Builder|MollieInvoice wherePackageType($value)
 * @method static Builder|MollieInvoice wherePayDate($value)
 * @method static Builder|MollieInvoice whereTransactionId($value)
 * @method static Builder|MollieInvoice whereUpdatedAt($value)
 * @mixin Eloquent
 */
class MollieInvoice extends BaseModel
{
    // Dates to be automatically cast to Carbon instances
    protected $dates = [
        'pay_date',
        'next_pay_date',
    ];

    // Additional casting for datetime fields
    protected $casts = [
        'pay_date' => 'datetime',
        'next_pay_date' => 'datetime',
    ];

    /**
     * Relationship to the company associated with this invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relationship to the package associated with this invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
