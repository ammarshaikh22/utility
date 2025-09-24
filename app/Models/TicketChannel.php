<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\TicketChannel
 *
 * Represents the channels through which tickets are submitted (e.g., email, chat, phone).
 *
 * @property int $id
 * @property string $channel_name            // Name of the ticket channel
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $company_id            // Associated company ID
 *
 * Relations:
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $tickets  // Tickets associated with this channel
 * @property-read int|null $tickets_count                                              // Count of tickets in this channel
 * @property-read \App\Models\Company|null $company                                    // Associated company
 *
 * @mixin \Eloquent
 */
class TicketChannel extends BaseModel
{
    use HasCompany;

    /**
     * Get all tickets associated with this channel.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'channel_id');
    }
}
