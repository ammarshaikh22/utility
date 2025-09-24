<?php

namespace App\Models;

use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\LeadCustomForm
 *
 * This model represents a custom form field definition for leads.
 * It defines the default form fields (name, email, company, etc.)
 * and their configurations such as order, required status, and display name.
 *
 * @property int $id
 * @property string $field_display_name  // Display label for the field (e.g., "Name")
 * @property string $field_name          // Database/field identifier (e.g., "name")
 * @property int $field_order            // Field display order in the form
 * @property string $status              // Status (active/inactive)
 * @property int $required               // 1 = required, 0 = optional
 * @property int|null $company_id        // Company association
 * @property int|null $custom_fields_id  // Reference to custom fields table
 * @property int|null $added_by          // User who added the record
 * @property int|null $last_updated_by   // User who last updated the record
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\CustomField|null $customField
 * @property-read mixed $extras
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereFieldDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereFieldName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereFieldOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereCustomFieldsId($value)
 * @mixin \Eloquent
 */
class LeadCustomForm extends BaseModel
{
    use CustomFieldsTrait;
    use HasCompany;

    /**
     * Fields that cannot be mass-assigned
     */
    protected $guarded = ['id'];

    /**
     * Default form fields for leads.
     * Each entry defines:
     * - field_display_name: Label shown to users
     * - field_name: The internal field key
     * - field_order: Ordering in the form UI
     * - required: Whether the field must be filled
     * - status: Active/Inactive
     */
    const FORM_FIELDS = [
        [
            'status' => 'active',
            'field_display_name' => 'Name',
            'field_name' => 'name',
            'field_order' => 1,
            'required' => 1,
        ],
        [
            'status' => 'active',
            'field_display_name' => 'Email',
            'field_name' => 'email',
            'field_order' => 2,
            'required' => 0,
        ],
        [
            'field_display_name' => 'Company Name',
            'status' => 'active',
            'field_name' => 'company_name',
            'field_order' => 3,
            'required' => 0,
        ],
        [
            'field_display_name' => 'Website',
            'field_name' => 'website',
            'status' => 'active',
            'field_order' => 4,
            'required' => 0,
        ],
        [
            'field_display_name' => 'Address',
            'field_name' => 'address',
            'status' => 'active',
            'field_order' => 5,
            'required' => 0,
        ],
        [
            'field_display_name' => 'Mobile',
            'field_name' => 'mobile',
            'field_order' => 6,
            'status' => 'active',
            'required' => 0,
        ],
        [
            'field_display_name' => 'Message',
            'field_name' => 'message',
            'status' => 'active',
            'field_order' => 7,
            'required' => 0,
        ],
        [
            'field_display_name' => 'City',
            'status' => 'active',
            'field_name' => 'city',
            'field_order' => 1,
            'required' => 0,
        ],
        [
            'field_display_name' => 'State',
            'status' => 'active',
            'field_name' => 'state',
            'field_order' => 2,
            'required' => 0,
        ],
        [
            'field_display_name' => 'Country',
            'field_name' => 'country',
            'status' => 'active',
            'field_order' => 3,
            'required' => 0,
        ],
        [
            'field_display_name' => 'Postal Code',
            'field_name' => 'postal_code',
            'status' => 'active',
            'field_order' => 4,
            'required' => 0,
        ],
        [
            'field_display_name' => 'Source',
            'field_name' => 'source',
            'status' => 'active',
            'field_order' => 8,
            'required' => 0,
        ],
        [
            'field_display_name' => 'Product',
            'field_name' => 'product',
            'status' => 'active',
            'field_order' => 9,
            'required' => 0,
        ],
    ];

    /**
     * Relationship with CustomField
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'custom_fields_id');
    }
}
