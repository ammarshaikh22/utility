<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TicketTag
 *
 * Represents the association between a ticket and a tag.
 *
 * @property int $id
 * @property int $tag_id              // ID of the tag
 * @property int $ticket_id           // ID of the ticket
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $company_id     // Optional company ID
 * @property-read \App\Models\TicketTagList $tag   // The related tag
 * @property-read \App\Models\Company|null $company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTag whereCompanyId($value)
 * @mixin \Eloquent
 */
class TicketTag extends BaseModel
{
    use HasCompany;

    // Prevent mass assignment of the ID
    protected $guarded = ['id'];

    /**
     * Get the tag associated with this ticket.
     *
     * @return BelongsTo
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(TicketTagList::class, 'tag_id');
    }
}
