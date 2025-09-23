<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class LeadSetting
 *
 * This model represents the `lead_setting` table in the database.
 * It stores configuration or preferences related to the leads module.
 *
 * - Extends BaseModel (project’s custom base class, usually extending Eloquent Model).
 * - Uses Laravel's HasFactory trait for model factories.
 * - Uses HasCompany trait for multi-tenant/company-level scoping.
 *
 * @package App\Models
 */
class LeadSetting extends BaseModel
{
    use HasFactory, HasCompany;

    /**
     * The table associated with the model.
     *
     * By default, Laravel would assume "lead_settings" (plural),
     * but here we override it to use the singular table name.
     *
     * @var string
     */
    protected $table = 'lead_setting';
}
