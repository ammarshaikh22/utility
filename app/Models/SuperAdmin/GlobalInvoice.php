<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use App\Models\OfflinePaymentMethod;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;

class GlobalInvoice extends BaseModel
{
    // Automatically cast these fields to datetime instances
    protected $casts = [
        'pay_date' => 'datetime',
        'next_pay_date' => 'datetime',
    ];

    // Appended computed attributes
    protected $appends = ['invoice_number'];

    // Prevent mass assignment of the ID
    protected $guarded = ['id'];

    /**
     * Relation: Invoice belongs to a company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relation: Invoice belongs to a package
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Relation: Invoice belongs to a currency (including soft-deleted currencies)
     */
    public function currency()
    {
        return $this->belongsTo(GlobalCurrency::class)->withTrashed();
    }

    /**
     * Relation: Invoice belongs to a subscription
     */
    public function subscription()
    {
        return $this->belongsTo(GlobalSubscription::class);
    }

    /**
     * Relation: Invoice may use an offline payment method
     * Only methods without a company ID are included
     */
    public function offlinePaymentMethod()
    {
        return $this->belongsTo(OfflinePaymentMethod::class, 'offline_method_id')
                    ->withoutGlobalScopes()
                    ->whereNull('company_id');
    }

    /**
     * Accessor: Returns the invoice number
     * For Stripe invoices, uses stripe_invoice_number; otherwise, uses ID
     * Pads the number with leading zeros to ensure at least 2 digits
     */
    protected function invoiceNumber(): Attribute
    {
        return Attribute::make(
            get: function () {
                $invoiceNumber = ($this->gateway == 'stripe') 
                    ? $this->stripe_invoice_number 
                    : $this->id;

                return str($invoiceNumber)->padLeft(2, '0');
            },
        );
    }
}
