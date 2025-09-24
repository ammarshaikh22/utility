<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\RecurringInvoiceItems
 *
 * Represents an item in a recurring invoice.
 *
 * @property int $id Primary key.
 * @property int $invoice_recurring_id The ID of the recurring invoice this item belongs to.
 * @property string $item_name Name of the item.
 * @property float $quantity Quantity of the item.
 * @property float $unit_price Unit price of the item.
 * @property float $amount Total amount for this item (quantity * unit_price).
 * @property string|null $taxes Taxes applied to this item (if any).
 * @property string $type Type of the item (e.g., product, service).
 * @property string|null $item_summary Optional summary or description.
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when record was created.
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when record was last updated.
 * @property string|null $hsn_sac_code Optional HSN/SAC code for the item.
 * @property int|null $product_id Optional related product ID.
 * @property int|null $unit_id Optional related unit type ID.
 * @property \App\Models\UnitType|null $unit The related unit object.
 * @property mixed $recurringInvoiceItemImage The associated image for this item.
 * @property-read mixed $icon Icon attribute from IconTrait.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereInvoiceRecurringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringInvoiceItems whereUnitId($value)
 *
 * @mixin \Eloquent
 */
class RecurringInvoiceItems extends BaseModel
{
    // Table name in the database
    protected $table = 'invoice_recurring_items';

    // Fields that are not mass assignable
    protected $guarded = ['id'];

    /**
     * Get taxes by ID including trashed records.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function taxbyid($id)
    {
        return Tax::where('id', $id)->withTrashed();
    }

    /**
     * Get the image associated with this recurring invoice item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function recurringInvoiceItemImage(): HasOne
    {
        return $this->hasOne(RecurringInvoiceItemImage::class, 'invoice_recurring_item_id');
    }

    /**
     * Get the unit type associated with this item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }
}
