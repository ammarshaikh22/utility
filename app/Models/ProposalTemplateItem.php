<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\ProposalTemplateItem
 *
 * Represents an item within a proposal template.
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $proposal_template_id
 * @property string $hsn_sac_code
 * @property string $item_name
 * @property string $type
 * @property int $quantity
 * @property float $unit_price
 * @property float $amount
 * @property string|null $item_summary
 * @property string|null $taxes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProposalTemplateItemImage|null $proposalTemplateItemImage
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereProposalTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereUpdatedAt($value)
 * @property int|null $product_id
 * @property int|null $unit_id
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProposalTemplateItem whereUnitId($value)
 * @mixin \Eloquent
 */
class ProposalTemplateItem extends BaseModel
{
    // Guard the ID from mass assignment
    protected $guarded = ['id'];

    // Always load the related image with the item
    protected $with = ['proposalTemplateItemImage'];

    /**
     * Get the image associated with this template item.
     */
    public function proposalTemplateItemImage(): HasOne
    {
        return $this->hasOne(ProposalTemplateItemImage::class, 'proposal_template_item_id');
    }

    /**
     * Get tax details by tax ID, including trashed (soft-deleted) taxes.
     */
    public static function taxbyid($id)
    {
        return Tax::where('id', $id)->withTrashed();
    }

    /**
     * Get the unit type associated with this item.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }
}
