<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Trebol\Entrust\EntrustRole;

/**
 * App\Models\Role
 *
 * Represents a user role within the system with associated permissions.
 *
 * @property int $id Primary key.
 * @property string $name Internal name (slug) of the role.
 * @property string|null $display_name Human-readable name of the role.
 * @property string|null $description Optional description of the role.
 * @property int|null $company_id ID of the company associated with the role.
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when the role was created.
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when the role was last updated.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PermissionRole[] $permissions Direct relation to PermissionRole.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $rolePermissions Permissions associated via pivot table.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RoleUser[] $roleuser Relation to RoleUser entries.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users Users assigned to this role.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $unsyncedUsers Users not synced with permissions.
 * @property-read \App\Models\Company|null $company Company associated with this role.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCompanyId($value)
 *
 * @mixin \Eloquent
 */
class Role extends EntrustRole
{
    use HasCompany;

    // Mass assignable attributes
    protected $fillable = ['name', 'display_name', 'description'];

    /**
     * Mutator to automatically convert role name to slug format when set.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn($value) => str_slug($value),
        );
    }

    /**
     * One-to-many relation: role to permission_role entries.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(PermissionRole::class, 'role_id');
    }

    /**
     * Many-to-many relation: role to permissions through pivot table.
     */
    public function rolePermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /**
     * One-to-many relation: role to role_user entries.
     */
    public function roleuser(): HasMany
    {
        return $this->hasMany(RoleUser::class, 'role_id');
    }

    /**
     * Many-to-many relation: role to users through pivot table.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    /**
     * Get the permission type for a specific permission ID for this role.
     *
     * @param int $permissionId
     * @return int|false
     */
    public function permissionType($permissionId)
    {
        $permissionType = PermissionRole::where('role_id', $this->id)
            ->where('permission_id', $permissionId)
            ->first();

        return $permissionType ? $permissionType->permission_type_id : false;
    }

    /**
     * Many-to-many relation: get users not synced with permissions.
     */
    public function unsyncedUsers()
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->where('users.permission_sync', 0);
    }
}
