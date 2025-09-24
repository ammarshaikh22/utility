<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use App\Models\BaseModel;

class PaystackSubscription extends BaseModel
{
    // Automatically cast 'created_at' to a Carbon datetime object
    protected $dates = ['created_at'];

    // Ensure proper casting for 'created_at' (redundant here but safe)
    protected $casts = ['created_at'];

    // Specify custom table name for this model
    protected $table = 'paystack_subscriptions';

    /**
     * Relationship: Each PaystackSubscription belongs to a company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}