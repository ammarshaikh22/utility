<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use App\Models\BaseModel;

class GlobalSubscription extends BaseModel
{
    // Define table name explicitly
    protected $table = 'global_subscriptions';

    // Dates that should be automatically cast to Carbon instances
    protected $dates = ['created_at'];

    // Cast additional datetime fields
    protected $casts = [
        'pay_date' => 'datetime',
        'next_pay_date' => 'datetime',
        'subscribed_on_date' => 'datetime',
    ];

    // Guard 'id' to prevent mass assignment
    protected $guarded = ['id'];

    /**
     * Relationship to the company associated with this subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
