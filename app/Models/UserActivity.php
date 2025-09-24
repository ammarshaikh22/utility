<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\UserActivity
 *
 * Represents an activity performed by a user in the system.
 *
 * @property int $id                           // Primary key of the user activity
 * @property int $user_id                      // ID of the user who performed the activity
 * @property string $activity                  // Description or name of the activity
 * @property \Illuminate\Support\Carbon|null $created_at   // Timestamp when the activity was created
 * @property \Illuminate\Support\Carbon|null $updated_at   // Timestamp when the activity was last updated
 * @property-read mixed $icon                  // Computed or dynamic icon representation (read-only)
 * @property-read \App\Models\User $user      // Relation to the User model
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity newModelQuery()  
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity newQuery()      
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity query()         
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereActivity($value)  
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereCreatedAt($value) 
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereId($value) 
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereUpdatedAt($value) 
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereUserId($value) 
 * @property int|null $company_id              // Optional company ID associated with the activity
 * @property-read \App\Models\Company|null $company  // Relation to the Company model (if any)
 * @method static \Illuminate\Database\Eloquent\Builder|UserActivity whereCompanyId($value) 
 * @mixin \Eloquent
 */
class UserActivity extends BaseModel
{
    // Include HasCompany trait to handle company-related functionality
    use HasCompany;

    /**
     * Define a BelongsTo relationship with the User model.
     * Removes the ActiveScope global constraint when fetching the user.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')
                    ->withoutGlobalScope(ActiveScope::class); // Ignore the ActiveScope when querying
    }

}
