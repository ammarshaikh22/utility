<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\RecurringInvoice
 *
 * Represents a recurring invoice configuration for a client/project.
 *
 * @property int $id
 * @property int|null $currency_id ID of the associated currency.
 * @property int|null $project_id ID of the related project.
 * @property int|null $client_id ID of the client receiving the invoice.
 * @property int|null $user_id ID of the user associated with this invoice.
 * @property int|null $created_by ID of the user who created the recurring invoice.
 * @property int|null $unit_id Unit type ID (optional).
 * @property \Illuminate\Support\Carbon $issue_date Date the invoice is issued.
 * @property \Illuminate\Support\Carbon $next_invoice_date Next scheduled invoice date.
 * @property \Illuminate\Support\Carbon $due_date Invoice due date.
 * @property float $sub_total Subtotal amount.
 * @property float $total Total invoice amount.
 * @property float $discount Discount applied.
 * @property string $discount_type Type of discount (percentage/fixed).
 * @property string $status Invoice status (active, paused, etc.).
 * @property string|null $file Attached file name.
 * @property string|null $file_original_name Original file name.
 * @property string|null $note Additional notes.
 * @property string $show_shipping_address Whether to show the shipping address.
 * @property int|null $day_of_month Billing day of the month.
 * @property int|null $day_of_week Billing day of the week.
 * @property string|null $payment_method Payment method.
 * @property string $rotation Billing rotation (daily, weekly, monthly, etc.).
 * @property int|null $billing_cycle Number of cycles for recurring invoice.
 * @property int $client_can_stop Whether client can stop the recurring invoice.
 * @property int $unlimited_recurring Flag for unlimited recurring.
 * @property string|null $deleted_at Soft delete timestamp.
 * @property string|null $shipping_address Client shipping address.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by ID of user who added the invoice.
 * @property int|null $last_updated_by ID of user who last updated the invoice.
 * @property string $calculate_tax Whether to calculate tax.
 * @property int|null $company_id ID of associated company.
 * @property int $immediate_invoice Flag to indicate immediate invoice generation.
 * @property int|null $bank_account_id Bank account ID for payment.
 *
 * @property-read \App\Models\User|null $client Related client.
 * @property-read \App\Models\ClientDetails|null $clientdetails Client details.
 * @property-read \App\Models\Currency|null $currency Currency of the invoice.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RecurringInvoiceItems[] $items Invoice line items.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $recurrings Generated invoices.
 * @property-read \App\Models\Project|null $project Related project.
 * @property-read \App\Models\UnitType|null $units Unit type.
 *
 * @property-read mixed $icon Icon attribute.
 * @property-read mixed $issue_on Formatted issue date.
 * @property-read mixed $total_amount Formatted total amount with currency.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereBillingCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereClientCanStop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereDayOfMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereFileOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereRotation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereShowShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereUnlimitedRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereCalculateTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereImmediateInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereNextInvoiceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoice whereBankAccountId($value)
 *
 * @mixin \Eloquent
 */
class RecurringInvoice extends BaseModel
{
    use Notifiable, HasCompany;

    protected $table = 'invoice_recurring';

    // Automatically cast these fields to Carbon instances
    protected $casts = [
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
        'next_invoice_date' => 'datetime',
    ];

    // Append custom attributes to the model
    protected $appends = ['total_amount', 'issue_on'];

    // Always eager load the client relation
    protected $with = ['client'];

    // Define colors for different rotation types
    const ROTATION_COLOR = [
        'daily' => 'success',
        'weekly' => 'info',
        'bi-weekly' => 'warning',
        'monthly' => 'secondary',
        'quarterly' => 'light',
        'half-yearly' => 'dark',
        'annually' => 'success',
    ];

    // Relations
    public function recurrings(): HasMany
    {
        return $this->hasMany(Invoice::class, 'invoice_recurring_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function clientdetails(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client_id', 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RecurringInvoiceItems::class, 'invoice_recurring_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function units(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    // Accessors
    public function getTotalAmountAttribute()
    {
        if (!is_null($this->total) && !is_null($this->currency->currency_symbol)) {
            return $this->currency->currency_symbol . $this->total;
        }
        return '';
    }

    public function getIssueOnAttribute()
    {
        if (is_null($this->issue_date)) {
            return '';
        }
        return Carbon::parse($this->issue_date)->format('d F, Y');
    }
}
