<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use App\Models\Currency;
use App\Models\BaseModel;

class RazorpaySubscription extends BaseModel
{
    // Cast created_at as a Carbon date
    protected $dates = ['created_at'];

    // Explicitly cast created_at
    protected $casts = ['created_at'];

    // Specify the table name
    protected $table = 'razorpay_subscriptions';

    /**
     * Relationship: Each subscription belongs to a company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relationship: Each subscription has a currency
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
