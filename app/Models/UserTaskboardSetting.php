<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\UserTaskboardSetting
 *
 * Represents a user's personal settings for taskboard columns, 
 * such as collapsed state of each board column.
 *
 * @property int $id                       // Primary key
 * @property int $user_id                  // ID of the user who owns this setting
 * @property int $board_column_id          // ID of the board column
 * @property int $collapsed                // Whether the column is collapsed (1) or expanded (0)
 * @property \Illuminate\Support\Carbon|null $created_at // Timestamp when the record was created
 * @property \Illuminate\Support\Carbon|null $updated_at // Timestamp when the record was updated
 * @property int|null $company_id          // Associated company ID (optional)
 * @property-read \App\Models\Company|null $company // Related company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting newModelQuery() // Start a new model query
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting newQuery()      // Start a new query builder
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting query()         // Get query builder for this model
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereBoardColumnId($value) // Filter by board_column_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereCollapsed($value)      // Filter by collapsed
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereCreatedAt($value)      // Filter by created_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereId($value)             // Filter by ID
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereUpdatedAt($value)      // Filter by updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereUserId($value)         // Filter by user_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskboardSetting whereCompanyId($value)      // Filter by company_id
 * @mixin \Eloquent
 */
class UserTaskboardSetting extends BaseModel
{
    use HasFactory, HasCompany;

    // Guard the primary key from mass assignment
    protected $guarded = ['id'];
}
