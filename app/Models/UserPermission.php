<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\UserPermission
 *
 * Represents the permissions assigned to a user, linking users to permissions and permission types.
 *
 * @property int $id                       // Primary key
 * @property int $user_id                  // ID of the user who has this permission
 * @property int $permission_id            // ID of the permission
 * @property int $permission_type_id       // ID of the permission type
 * @property int $customised_permissions   // Flag or bitmask for customised permissions
 * @property string $name                  // Name of the permission
 * @property \Illuminate\Support\Carbon|null $created_at // Timestamp when the record was created
 * @property \Illuminate\Support\Carbon|null $updated_at // Timestamp when the record was updated
 * @property-read \App\Models\User $user  // Related user
 * @property-read \App\Models\Permission $permission // Related permission
 * @property-read \App\Models\PermissionType $type   // Related permission type
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission newModelQuery() // Start a new model query
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission newQuery()      // Start a new query builder
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission query()         // Get query builder for this model
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereCreatedAt($value) // Filter by created_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereId($value)        // Filter by ID
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission wherePermissionId($value) // Filter by permission_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission wherePermissionTypeId($value) // Filter by permission_type_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereUpdatedAt($value)     // Filter by updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereUserId($value)        // Filter by user_id
 * @mixin \Eloquent
 */
class UserPermission extends BaseModel
{
    use HasFactory;

    // Mass assignable fields
    protected $fillable = ['user_id', 'permission_id', 'permission_type_id'];

    /**
     * Get the permission type associated with this user permission.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(PermissionType::class, 'permission_type_id');
    }

    /**
     * Get the permission associated with this user permission.
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    /**
     * Get the user that owns this permission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
