<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use App\Models\BaseModel;

class Subscription extends BaseModel
{
    // Automatically cast 'created_at' as a Carbon datetime object
    protected $dates = ['created_at'];

    // Define casts
    protected $casts = ['created_at'];

    /**
     * Define relationship to the Company model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
