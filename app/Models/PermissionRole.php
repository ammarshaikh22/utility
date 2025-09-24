<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use App\Scopes\SuperAdminModuleScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PermissionRole
 *
 * Pivot model that connects **Permissions ↔ Roles** with a specific permission type.
 * Example: Role "Employee" may have "view_projects" permission with type "OWNED".
 *
 * @property int $permission_id ID of the permission
 * @property int $role_id ID of the role
 * @property int $permission_type_id Defines type of access (ALL, OWNED, ADDED, BOTH, NONE)
 *
 * @property-read \App\Models\PermissionType $permissionType Access type (relation)
 * @property-read \App\Models\Permission $permission The actual permission linked
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole wherePermissionTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionRole whereRoleId($value)
 *
 * @mixin \Eloquent
 */
class PermissionRole extends BaseModel
{
    /**
     * The pivot table for this model
     */
    protected $table = 'permission_role';

    /**
     * Mass assignable fields
     */
    protected $fillable = ['role_id', 'permission_id', 'permission_type_id'];

    /**
     * Disable timestamps (since pivot tables usually don’t have them)
     */
    public $timestamps = false;

    /**
     * Relationship: Each role-permission pair has a permission type
     */
    public function permissionType(): BelongsTo
    {
        return $this->belongsTo(PermissionType::class, 'permission_type_id');
    }

    /**
     * Relationship: Each entry belongs to a permission
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    /**
     * Default permissions for EMPLOYEE roles
     * Employees typically have restricted or owned access
     */
    public static function employeeRolePermissions()
    {
        $employeePermissionsArray = [
            'view_projects' => PermissionType::OWNED,
            'view_project_files' => PermissionType::ALL,
            'add_project_files' => PermissionType::ALL,
            'edit_project_files' => PermissionType::ADDED,
            'delete_project_files' => PermissionType::ADDED,
            // … continues with detailed CRUD mappings
            'view_immigration' => PermissionType::OWNED,
            'add_immigration' => PermissionType::OWNED,
            'edit_immigration' => PermissionType::OWNED,
            'delete_immigration' => PermissionType::OWNED,
        ];

        return $employeePermissionsArray;
    }

    /**
     * Default permissions for CLIENT roles
     * Clients usually get access only to OWNED data (their projects, invoices, etc.)
     */
    public static function clientRolePermissions()
    {
        $clientPermissionsArray = [
            'view_projects' => PermissionType::OWNED,
            'view_project_files' => PermissionType::ALL,
            'add_project_files' => PermissionType::ALL,
            'edit_project_files' => PermissionType::ADDED,
            'delete_project_files' => PermissionType::ADDED,
            // … continues with client-specific rules
            'view_order' => PermissionType::OWNED,
        ];

        return $clientPermissionsArray;
    }

    /**
     * Insert default module-role permissions into DB
     * - Admin role gets ALL permissions by default
     * - Other roles get NONE by default (unless explicitly set)
     *
     * @param string $moduleName Module to assign permissions for
     * @param int $companyId Company context
     */
    public static function insertModuleRolePermission($moduleName, $companyId)
    {
        // Get module and its permissions (ignores SuperAdmin-only scope)
        $modulePermissions = \App\Models\Module::withoutGlobalScope(SuperAdminModuleScope::class)
            ->with('permissionsAll')
            ->where('module_name', $moduleName)
            ->firstOrFail();

        // Assign full (ALL) permissions to ADMIN role
        $adminRole = Role::withoutGlobalScope(CompanyScope::class)
            ->with('roleuser', 'roleuser.user.roles')
            ->where('name', 'admin')
            ->where('company_id', $companyId)
            ->first();

        if ($adminRole) {
            // Clear old permissions for this module
            PermissionRole::whereHas('permission', function ($query) use ($modulePermissions) {
                $query->where('module_id', $modulePermissions->id);
            })->where('role_id', $adminRole->id)->delete();

            // Assign ALL permissions to Admin
            foreach ($modulePermissions->permissionsAll as $permission) {
                PermissionRole::create([
                    'permission_id' => $permission->id,
                    'role_id' => $adminRole->id,
                    'permission_type_id' => PermissionType::ALL
                ]);
            }

            // Also ensure each admin user has ALL permissions
            foreach ($adminRole->roleuser as $roleuser) {
                foreach ($modulePermissions->permissionsAll as $permission) {
                    UserPermission::firstOrCreate([
                        'permission_id' => $permission->id,
                        'user_id' => $roleuser->user_id,
                        'permission_type_id' => PermissionType::ALL
                    ]);
                }
            }
        }

        // Assign default (NONE) permissions to all other roles
        $otherRoles = Role::withoutGlobalScope(CompanyScope::class)
            ->with('roleuser', 'roleuser.user.roles')
            ->where('name', '<>', 'admin')
            ->where('company_id', $companyId)
            ->get();

        foreach ($otherRoles as $role) {
            foreach ($modulePermissions->permissionsAll as $permission) {
                $permissionRole = PermissionRole::where('permission_id', $permission->id)
                    ->where('role_id', $role->id)
                    ->first();

                if (!$permissionRole) {
                    PermissionRole::create([
                        'permission_id' => $permission->id,
                        'role_id' => $role->id,
                        'permission_type_id' => PermissionType::NONE
                    ]);
                }
            }

            // Ensure users under this role also have default (NONE) permissions
            foreach ($role->roleuser as $roleuser) {
                foreach ($modulePermissions->permissionsAll as $permission) {
                    $userPermission = UserPermission::where('permission_id', $permission->id)
                        ->where('user_id', $roleuser->user_id)
                        ->first();

                    if (!$userPermission) {
                        UserPermission::create([
                            'permission_id' => $permission->id,
                            'user_id' => $roleuser->user_id,
                            'permission_type_id' => PermissionType::NONE
                        ]);
                    }
                }
            }
        }
    }
}
