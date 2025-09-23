<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\RemovalRequestLead
 *
 * Represents a removal request associated with a lead in the system.
 *
 * @property int $id Primary key.
 * @property string $name Name or title of the removal request.
 * @property string $description Detailed description of the removal request.
 * @property int|null $lead_id ID of the associated lead (nullable).
 * @property int|null $company_id ID of the associated company (nullable).
 * @property string $status Status of the request (e.g., pending, approved, rejected).
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when the record was created.
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when the record was last updated.
 * @property-read mixed $icon Icon attribute from IconTrait (if used in BaseModel).
 * @property-read \App\Models\Deal|null $lead The lead associated with this removal request.
 * @property-read \App\Models\Company|null $company The company associated with this removal request.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead query()
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RemovalRequestLead whereCompanyId($value)
 *
 * @mixin \Eloquent
 */
class RemovalRequestLead extends BaseModel
{
    // Include HasCompany trait to associate removal requests with a company
    use HasCompany;

    // Explicit table name since it differs from Laravel's pluralization convention
    protected $table = 'removal_requests_lead';

    /**
     * Get the lead associated with this removal request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }
}
