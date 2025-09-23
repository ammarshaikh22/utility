<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\LeadAgent
 *
 * This model represents a Lead Agent in the system.
 * A Lead Agent is typically linked with a user and manages multiple leads.
 *
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereUserId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lead> $leads
 * @property-read int|null $leads_count
 * @mixin \Eloquent
 */
class LeadAgent extends BaseModel
{

    // Trait to associate Lead Agents with a company
    use HasCompany;

    // Explicitly defining the database table
    protected $table = 'lead_agents';

    // Protect the primary key from mass assignment
    protected $guarded = ['id'];

    /**
     * Relationship: LeadAgent belongs to a User
     * Removes the ActiveScope so inactive users can also be retrieved.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Relationship: LeadAgent has many Leads (Deals)
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Deal::class, 'agent_id');
    }

}
