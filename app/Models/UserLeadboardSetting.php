<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\UserLeadboardSetting
 *
 * Represents a user's custom settings for leadboard columns (e.g., collapsed/expanded state).
 *
 * @property int $id                       // Primary key
 * @property int $user_id                  // ID of the user who owns this setting
 * @property int $board_column_id          // ID of the board column this setting applies to
 * @property int $collapsed                // 1 if the column is collapsed, 0 if expanded
 * @property int|null $company_id          // Company ID (optional for multi-company setup)
 * @property int|null $pipeline_stage_id   // ID of the pipeline stage (optional)
 * @property \Illuminate\Support\Carbon|null $created_at // Timestamp when the setting was created
 * @property \Illuminate\Support\Carbon|null $updated_at // Timestamp when the setting was last updated
 * @property-read \App\Models\Company|null $company // Related company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting newModelQuery() // Start a new model query
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting newQuery()      // Start a new query builder
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting query()         // Get query builder for this model
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereBoardColumnId($value) // Filter by board_column_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereCollapsed($value)      // Filter by collapsed
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereCreatedAt($value)     // Filter by created_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereId($value)            // Filter by ID
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereUpdatedAt($value)     // Filter by updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereUserId($value)        // Filter by user_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting whereCompanyId($value)     // Filter by company_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserLeadboardSetting wherePipelineStageId($value) // Filter by pipeline_stage_id
 * @mixin \Eloquent
 */
class UserLeadboardSetting extends BaseModel
{
    use HasFactory;  // Adds factory support
    use HasCompany;  // Adds multi-company support

    protected $guarded = ['id']; // Prevent mass assignment on ID
}
