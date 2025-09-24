<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\TicketReplyTemplate
 *
 * Represents a template for ticket replies, used to standardize responses.
 *
 * @property int $id
 * @property string $reply_heading               // Heading/title of the template
 * @property string $reply_text                  // Body/content of the template
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $company_id                // Associated company, if multi-tenant
 *
 * Relations:
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $icon                    // Optional icon for display
 *
 * @mixin \Eloquent
 */
class TicketReplyTemplate extends BaseModel
{
    use HasCompany;

    // Currently, no additional methods or relations defined
}
