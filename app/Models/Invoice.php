<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

/**
 * Invoice Model
 *
 * Represents invoices in the system with relationships to projects, clients,
 * payments, currencies, etc. Handles calculations, formatting, and query scopes.
 */
class Invoice extends BaseModel
{
    // Traits for notifications, custom fields, and company association
    use Notifiable;
    use CustomFieldsTrait;
    use HasCompany;

    // Casts for automatic date handling
    protected $casts = [
        'issue_date'   => 'datetime',
        'due_date'     => 'datetime',
        'last_viewed'  => 'datetime',
    ];

    // Extra computed attributes that will be appended automatically
    protected $appends = ['total_amount', 'issue_on'];

    // Always eager load currency relation with invoices
    protected $with = ['currency'];

    // Constant used for custom fields association
    const CUSTOM_FIELD_MODEL = 'App\Models\Invoice';

    /**
     * -------------------------------
     * Relationships
     * -------------------------------
     */

    // Each invoice may belong to a project
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }

    // Invoice belongs to a client (user) - without ActiveScope
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScope(ActiveScope::class);
    }

    // Invoice belongs to client details
    public function clientdetails(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client_id', 'user_id');
    }

    // Invoice can have multiple credit notes
    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNotes::class);
    }

    // Invoice can have recurring invoices (self relation)
    public function recurrings(): HasMany
    {
        return $this->hasMany(Invoice::class, 'parent_id');
    }

    // Invoice can have multiple items
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItems::class, 'invoice_id');
    }

    // Invoice payments (ordered by latest payment date)
    public function payment(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id')->orderByDesc('paid_on');
    }

    // Invoice belongs to a currency
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    // Invoice may be linked to an order
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Invoice can be linked to an estimate
    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class, 'estimate_id');
    }

    // Company address associated with invoice
    public function address(): BelongsTo
    {
        return $this->belongsTo(CompanyAddress::class, 'company_address_id');
    }

    // Bank account used for invoice payments
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    // Payment detail for invoice
    public function invoicePaymentDetail()
    {
        return $this->belongsTo(InvoicePaymentDetail::class, 'invoice_payment_id');
    }

    // Invoice files (e.g. attachments) ordered by latest
    public function files(): HasMany
    {
        return $this->hasMany(InvoiceFiles::class, 'invoice_id')->orderByDesc('id');
    }

    /**
     * -------------------------------
     * Scopes
     * -------------------------------
     */

    // Scope for pending invoices (unpaid or partial)
    public function scopePending($query)
    {
        return $query->where(function ($q) {
            $q->where('invoices.status', 'unpaid')
              ->orWhere('invoices.status', 'partial');
        });
    }

    /**
     * -------------------------------
     * Static Methods
     * -------------------------------
     */

    // Get all invoices of a given client (joined with projects)
    public static function clientInvoices($clientId)
    {
        return Invoice::join('projects', 'projects.id', '=', 'invoices.project_id')
            ->select('projects.project_name', 'invoices.*')
            ->where('projects.client_id', $clientId)
            ->get();
    }

    // Get last invoice number, optionally filtered by company
    public static function lastInvoiceNumber($companyId = null)
    {
        if ($companyId) {
            return (int)Invoice::where('company_id', $companyId)
                ->orderBy('id', 'desc')
                ->first()?->original_invoice_number ?? 0;
        }

        return (int)Invoice::orderBy('id', 'desc')->first()?->original_invoice_number ?? 0;
    }

    /**
     * -------------------------------
     * Business Logic
     * -------------------------------
     */

    // Total credits applied to this invoice
    public function appliedCredits()
    {
        return Payment::where('invoice_id', $this->id)->sum('amount');
    }

    // Amount still due for this invoice
    public function amountDue()
    {
        $due = $this->total - ($this->amountPaid());
        return max($due, 0); // Ensure no negative values
    }

    // Amount actually paid (only completed payments)
    public function amountPaid()
    {
        return $this->payment->where('status', 'complete')->sum('amount');
    }

    // Get total paid (all payments regardless of status)
    public function getPaidAmount()
    {
        return $this->payment->sum('amount');
    }

    /**
     * -------------------------------
     * Accessors
     * -------------------------------
     */

    // Format total amount with currency symbol
    public function getTotalAmountAttribute()
    {
        if (!is_null($this->total) && !is_null($this->currency->currency_symbol)) {
            return $this->currency->currency_symbol . $this->total;
        }
        return '';
    }

    // Format issue date for display
    public function getIssueOnAttribute()
    {
        if (is_null($this->issue_date)) {
            return '';
        }
        return Carbon::parse($this->issue_date)->format('d F, Y');
    }

    // Format invoice number based on company settings
    public function formatInvoiceNumber()
    {
        $invoiceSettings = company() ? company()->invoiceSetting : $this->company->invoiceSetting;
        return \App\Helper\NumberFormat::invoice($this->invoice_number, $invoiceSettings);
    }

    // Get downloadable file URL if available
    public function getDownloadFileUrlAttribute()
    {
        return ($this->downloadable_file)
            ? asset_url_local_s3(InvoiceFiles::FILE_PATH . '/' . $this->downloadable_file)
            : null;
    }

    public function files(): HasMany
    {
        return $this->hasMany(InvoiceFiles::class, 'invoice_id')->orderByDesc('id');
    }
}
