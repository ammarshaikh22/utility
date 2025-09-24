<?php

namespace App\Models\SuperAdmin;

use App\Models\Invoice;
use App\Observers\SuperAdmin\InvoicePaymentReceivedObserver;
use App\Models\BaseModel;

class ClientPayment extends BaseModel
{
    // Specify the table name for this model
    protected $table = 'payments';

    // Dates that should be cast to Carbon instances
    protected $dates = ['paid_on'];

    // Cast attributes to specific data types
    protected $casts = [
        'paid_on' => 'datetime',
    ];

    /**
     * Boot method to attach model observers.
     * Observers can handle actions like sending notifications or updating related records
     * when a model event (create, update, delete) occurs.
     */
    protected static function boot()
    {
        parent::boot();

        // Attach observer to handle actions when a client payment is received
        static::observe(InvoicePaymentReceivedObserver::class);
    }

    /**
     * Define the relationship with the Invoice model.
     * Each client payment belongs to a single invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
