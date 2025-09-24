<?php

namespace App\Models;

use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TicketCustomForm
 *
 * Represents custom fields associated with tickets, allowing dynamic forms for tickets.
 *
 * @property int $id
 * @property string $field_display_name      // The label displayed to users
 * @property string $field_name              // Internal field name for the database
 * @property string $field_type              // Type of the field (text, number, dropdown, etc.)
 * @property int $field_order                // Order of the field in the form
 * @property string $status                  // Status of the field (active/inactive)
 * @property int $required                   // Whether this field is required (1 = yes, 0 = no)
 * @property int|null $company_id            // Associated company
 * @property int|null $custom_fields_id      // Linked custom field ID
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * Relations:
 * @property-read \App\Models\Company|null $company            // The company this form belongs to
 * @property-read \App\Models\CustomField|null $customField   // The actual custom field linked
 * @property-read mixed $extras                                 // Extra metadata or computed values
 *
 * @mixin \Eloquent
 */
class TicketCustomForm extends BaseModel
{
    use CustomFieldsTrait;
    use HasCompany;

    // Protect the ID field from mass assignment
    protected $guarded = ['id'];

    /**
     * Relation: get the linked custom field
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'custom_fields_id');
    }
}
