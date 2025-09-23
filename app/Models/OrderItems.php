<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\OrderItems
 *
 * Represents individual items belonging to an order.
 *
 * @property int $id
 * @property int $order_id
 * @property string $item_name
 * @property string|null $item_summary
 * @property string $type
 * @property int $quantity
 * @property int $unit_price
 * @property float $amount
 * @property string|null $hsn_sac_code
 * @property string|null $taxes JSON-encoded list of tax IDs
 * @property int|null $product_id
 * @property int|null $unit_id
 * @property string|null $sku
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\OrderItemImage|null $orderItemImage
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\UnitType|null $unit
 * @property-read mixed $tax_list  Computed list of applied taxes
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItems whereSku($value)
 *
 * @mixin \Eloquent
 */
class OrderItems extends BaseModel
{
    /**
     * Mass assignable fields.
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'item_name',
        'item_summary',
        'type',
        'quantity',
        'unit_price',
        'amount',
        'hsn_sac_code',
        'taxes',
        'unit_id',
        'sku',
        'field_order',
    ];

    /**
     * Always eager-load related models for optimization.
     */
    protected $with = ['orderItemImage', 'product'];

    /**
     * Get a Tax model by its ID (including trashed).
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function taxbyid($id)
    {
        return Tax::where('id', $id)->withTrashed();
    }

    /**
     * One-to-one relation: OrderItemImage linked to this order item.
     */
    public function orderItemImage(): HasOne
    {
        return $this->hasOne(OrderItemImage::class, 'order_item_id');
    }

    /**
     * Belongs-to relation: Product linked to this order item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Accessor: Return formatted tax list as a string.
     *
     * @return string
     */
    public function getTaxListAttribute()
    {
        $orderItem = OrderItems::findOrFail($this->id);
        $taxes = '';

        if ($orderItem && $orderItem->taxes) {
            $taxIds = json_decode($orderItem->taxes, true);

            if (!is_null($taxIds)) {
                $numItems = count($taxIds);

                foreach ($taxIds as $index => $taxId) {
                    $tax = $this->taxbyid($taxId)->first();

                    if ($tax) {
                        $taxes .= $tax->tax_name . ': ' . $tax->rate_percent . '%';
                        $taxes .= ($index + 1 != $numItems) ? ', ' : '';
                    }
                }
            }
        }

        return $taxes;
    }

    /**
     * Belongs-to relation: UnitType linked to this order item.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }
}
