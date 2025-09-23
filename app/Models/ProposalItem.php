<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ProposalItem
 *
 * Represents a single item within a Proposal.
 *
 * @property int $id
 * @property int $proposal_id
 * @property string $item_name
 * @property string $type
 * @property float $quantity
 * @property float $unit_price
 * @property float $amount
 * @property string|null $item_summary
 * @property string|null $taxes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $hsn_sac_code
 * @property int|null $product_id
 * @property int|null $unit_id
 * @property-read mixed $icon
 * @property-read \App\Models\ProposalItemImage|null $proposalItemImage
 * @property-read mixed $tax_list
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereProposalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalItem whereUnitId($value)
 * @mixin \Eloquent
 */
class ProposalItem extends BaseModel
{
    // Protects 'id' from mass assignment
    protected $guarded = ['id'];

    // Always load the related ProposalItemImage when retrieving a ProposalItem
    protected $with = ['proposalItemImage'];

    /**
     * One-to-one relationship with ProposalItemImage
     * Each proposal item can have a single associated image.
     */
    public function proposalItemImage(): HasOne
    {
        return $this->hasOne(ProposalItemImage::class, 'proposal_item_id');
    }

    /**
     * Static helper to retrieve a Tax record by ID, including trashed ones
     */
    public static function taxbyid($id)
    {
        return Tax::where('id', $id)->withTrashed();
    }

    /**
     * Belongs to relationship with UnitType
     * Each proposal item may have a unit associated.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    /**
     * Accessor to get a formatted list of taxes applied to this proposal item
     *
     * Loops through the JSON-encoded taxes and builds a string like:
     * "GST: 5%, VAT: 10%"
     */
    public function getTaxListAttribute()
    {
        $proposalItem = ProposalItem::findOrFail($this->id); // Ensure item exists
        $taxes = '';

        if ($proposalItem && $proposalItem->taxes) {
            $numItems = count(json_decode($proposalItem->taxes));

            if (!is_null($proposalItem->taxes)) {
                foreach (json_decode($proposalItem->taxes) as $index => $tax) {
                    // Fetch the tax details
                    $tax = $this->taxbyid($tax)->first();
                    $taxes .= $tax->tax_name . ': ' . $tax->rate_percent . '%';

                    // Add comma separator except after last item
                    $taxes = ($index + 1 != $numItems) ? $taxes . ', ' : $taxes;
                }
            }
        }

        return $taxes;
    }
}
