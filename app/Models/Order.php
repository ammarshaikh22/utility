<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Order
 *
 * Represents an order in the system including client details,
 * items, payments, invoices, and related company/unit information.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int|null $client_id
 * @property string $order_date
 * @property float $sub_total
 * @property float $total
 * @property float $due_amount
 * @property string $status
 * @property int|null $currency_id
 * @property string $show_shipping_address
 * @property string|null $note
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float $discount
 * @property string $discount_type
 * @property int|null $company_id
 * @property int|null $company_address_id
 * @property string|null $custom_order_number
 * @property string|null $original_order_number
 *
 * @property-read \App\Models\User|null $client
 * @property-read \App\Models\ClientDetails|null $clientdetails
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OrderItems[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoice
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[] $payment
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\CompanyAddress|null $address
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\UnitType|null $unit
 *
 * @mixin \Eloquent
 */
class Order extends BaseModel
{
    use HasCompany, CustomFieldsTrait;

    /**
     * Indicates this model supports custom fields.
     */
    const CUSTOM_FIELD_MODEL = 'App\Models\Order';

    /**
     * Relationship: The client who placed the order.
     *
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')
            ->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Relationship: Extra client details (from ClientDetails).
     *
     * @return BelongsTo
     */
    public function clientdetails(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client_id', 'user_id');
    }

    /**
     * Relationship: Items included in this order.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    /**
     * Relationship: Payments related to this order (via invoice).
     *
     * @return HasMany
     */
    public function payment(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id')
            ->orderByDesc('paid_on');
    }

    /**
     * Relationship: Invoice associated with this order.
     *
     * @return HasOne
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'order_id');
    }

    /**
     * Relationship: Currency used for this order.
     *
     * @return BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * Relationship: Company address associated with this order.
     *
     * @return BelongsTo
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(CompanyAddress::class, 'company_address_id');
    }

    /**
     * Get the last original order number from the database.
     *
     * @return int
     */
    public static function lastOrderNumber(): int
    {
        return (int) Order::latest()->first()?->original_order_number ?? 0;
    }

    /**
     * Relationship: Unit type (if applicable).
     *
     * @return BelongsTo
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    /**
     * Format the order number based on company or system settings.
     *
     * @return string
     */
    public function formatOrderNumber(): string
    {
        $orderSettings = (company()) 
            ? company()->invoiceSetting 
            : $this->company->invoiceSetting;

        return \App\Helper\NumberFormat::order(
            $this->order_number,
            $orderSettings
        );
    }

    /**
     * Relationship: Project associated with this order.
     * Includes trashed (soft-deleted) projects.
     *
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }
}
