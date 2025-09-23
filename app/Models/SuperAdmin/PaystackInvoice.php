<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use App\Models\BaseModel;

/**
 * App\Models\SuperAdmin\PaystackInvoice
 *
 * @property int $id
 * @property int $company_id
 * @property int $package_id
 * @property string|null $transaction_id
 * @property string|null $amount
 * @property Carbon $pay_date
 * @property Carbon|null $next_pay_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read Package $package
 * @method static Builder|PaystackInvoice newModelQuery()
 * @method static Builder|PaystackInvoice newQuery()
 * @method static Builder|PaystackInvoice query()
 * @method static Builder|PaystackInvoice whereAmount($value)
 * @method static Builder|PaystackInvoice whereCompanyId($value)
 * @method static Builder|PaystackInvoice whereCreatedAt($value)
 * @method static Builder|PaystackInvoice whereId($value)
 * @method static Builder|PaystackInvoice whereNextPayDate($value)
 * @method static Builder|PaystackInvoice wherePackageId($value)
 * @method static Builder|PaystackInvoice wherePayDate($value)
 * @method static Builder|PaystackInvoice whereTransactionId($value)
 * @method static Builder|PaystackInvoice whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PaystackInvoice extends BaseModel
{
    // Automatically cast these columns to Carbon datetime objects
    protected $dates = [
        'pay_date',
        'next_pay_date',
    ];

    protected $casts = [
        'pay_date' => 'datetime',
        'next_pay_date' => 'datetime',
    ];

    /**
     * Relationship: Each PaystackInvoice belongs to a company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relationship: Each PaystackInvoice belongs to a subscription package
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
