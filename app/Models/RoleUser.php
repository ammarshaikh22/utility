<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\RoleUser
 *
 * Represents the pivot table between users and roles.
 *
 * @property int $user_id ID of the user.
 * @property int $role_id ID of the role.
 * @property-read \App\Models\User $user The user associated with this role.
 * @property-read \App\Models\Role $role The role associated with this user.
 * @property-read mixed $icon Optional icon attribute from BaseModel or traits.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleUser whereUserId($value)
 *
 * @mixin \Eloquent
 */
class RoleUser extends BaseModel
{
    // Pivot table name
    protected $table = 'role_user';

    // Disable timestamps for pivot table
    public $timestamps = false;

    /**
     * BelongsTo relation: each RoleUser entry belongs to a User.
     * Removes the ActiveScope so inactive users can still be accessed.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * BelongsTo relation: each RoleUser entry belongs to a Role.
     * NOTE: There is a small issue in your original code: 
     * you used 'user_id' instead of 'role_id'. It should be:
     * return $this->belongsTo(Role::class, 'role_id');
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
