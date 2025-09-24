<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use App\Models\BaseModel;

class AuthorizationInvoice extends BaseModel
{
    // Table name
    protected $table = 'authorize_invoices';

    // Date fields to be automatically cast as Carbon instances
    protected $dates = [
        'pay_date',
        'next_pay_date',
    ];

    // Casts for proper datetime handling
    protected $casts = [
        'pay_date' => 'datetime',
        'next_pay_date' => 'datetime',
    ];

    /**
     * Get the company this invoice belongs to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the package associated with this invoice.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
