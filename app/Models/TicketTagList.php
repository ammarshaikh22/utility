<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\TicketTagList
 *
 * Represents a master list of all available ticket tags.
 *
 * @property int $id
 * @property string $tag_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $icon
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList whereTagName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketTagList whereCompanyId($value)
 * @mixin \Eloquent
 */
class TicketTagList extends BaseModel
{
    use HasCompany;

    // Table name
    protected $table = 'ticket_tag_list';

    // Prevent mass assignment of the ID
    protected $guarded = ['id'];
}
