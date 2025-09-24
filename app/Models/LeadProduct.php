<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class LeadProduct
 *
 * This model represents the pivot table `lead_products`, which defines the
 * many-to-many relationship between `deals` (leads) and `products`.
 * 
 * - Extends Laravel's Pivot class (specialized for pivot/intermediate tables).
 * - Stores which products are linked to a specific deal/lead.
 *
 * @property int $id Unique identifier for the pivot record
 * @property int $deal_id Foreign key referencing the related Deal
 * @property int $product_id Foreign key referencing the related Product
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when the record was created
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when the record was last updated
 *
 * @property-read \App\Models\Product $product The product associated with this record
 * @property-read \App\Models\Deal $lead The deal/lead associated with this record
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadProduct whereDealId($value)
 * 
 * @mixin \Eloquent
 */
class LeadProduct extends Pivot
{
    use HasFactory;

    /**
     * The attributes that are not mass assignable.
     *
     * In this case, only `id` is guarded,
     * allowing other fields to be mass assigned.
     */
    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     */
    protected $table = 'lead_products';

    /**
     * Relationship: A LeadProduct belongs to a Product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Relationship: A LeadProduct belongs to a Deal (Lead).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }
}
