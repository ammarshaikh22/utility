<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\UnitType
 *
 * Represents different unit types used across invoices, proposals, estimates, credit notes, orders, and recurring invoices.
 * Each unit type can be associated with multiple entities and optionally linked to a company.
 *
 * Properties:
 * @property int $id                     // Primary key
 * @property int|null $company_id        // Optional company association
 * @property string $unit_type           // Name of the unit type (e.g., "kg", "pcs")
 * @property int $default                // Indicates if this unit type is the default (1 = yes, 0 = no)
 * @property \Illuminate\Support\Carbon|null $created_at // Creation timestamp
 * @property \Illuminate\Support\Carbon|null $updated_at // Last update timestamp
 *
 * Relations:
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditNotes> $creditnoteitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EstimateItem> $estimateitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EstimateTemplate> $estimatetemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItems> $invoicesItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalItem> $proposalitems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProposalTemplate> $proposaltemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecurringInvoice> $recurringInvoice
 *
 * Query Scopes / Methods:
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType query()
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereUnitType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UnitType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UnitType extends BaseModel
{
    use HasCompany;

    // Define the database table explicitly
    protected $table = 'unit_types';

    // Primary key field
    protected $id = 'id';

    // Fillable fields for mass assignment
    protected $fillable = ['unit_type', 'company_id', 'default'];

    // Relations

    /**
     * All invoice items associated with this unit type
     */
    public function invoicesItems()
    {
        return $this->hasMany(InvoiceItems::class);
    }

    /**
     * All proposal items associated with this unit type
     */
    public function proposalitems()
    {
        return $this->hasMany(ProposalItem::class);
    }

    /**
     * All estimate items associated with this unit type
     */
    public function estimateitems()
    {
        return $this->hasMany(EstimateItem::class);
    }

    /**
     * All credit note items associated with this unit type
     */
    public function creditnoteitems()
    {
        return $this->hasMany(CreditNotes::class);
    }

    /**
     * All proposal templates associated with this unit type
     */
    public function proposaltemplate()
    {
        return $this->hasMany(ProposalTemplate::class);
    }

    /**
     * All estimate templates associated with this unit type
     */
    public function estimatetemplate()
    {
        return $this->hasMany(EstimateTemplate::class);
    }

    /**
     * All recurring invoices associated with this unit type
     */
    public function recurringInvoice()
    {
        return $this->hasMany(RecurringInvoice::class);
    }

    /**
     * All orders associated with this unit type
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
