<?php

namespace Database\Seeders;

/**
 * Employee Permission Seeder - assigns role-based permissions for Employee, Admin, Client
 * Configures secure access levels using Laravel's RBAC system
 */

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeePermissionSeeder extends Seeder
{
    /**
     * Permission Types: 1=Added, 2=Owned, 3=Both, 4=All, 5=None
     */
    protected array $permissionTypes = [
        'added' => 1,
        'owned' => 2,
        'both' => 3,
        'all' => 4,
        'none' => 5
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $this->command->info("   🔐 Seeding role permissions for company {$companyId}...");
        
        DB::beginTransaction();
        try {
            $this->insertUserRolePermission($companyId);
            DB::commit();
            $this->command->info("      ✅ All permissions assigned successfully!");
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Permission seeding failed for company ' . $companyId . ': ' . $e->getMessage());
            $this->command->error("      ❌ Permission seeding failed: " . $e->getMessage());
        }
    }

    /**
     * Main method: Setup permissions for Employee → Admin → Client roles
     */
    public function insertUserRolePermission($companyId)
    {
        // Get all company permissions (exclude superadmin modules)
        $allPermissions = Permission::whereHas('module', function ($query) {
            $query->withoutGlobalScopes()->where('is_superadmin', '0');
        })->get();

        $this->command->info("      📋 Loaded " . $allPermissions->count() . " permissions");

        // === EMPLOYEE ROLE ===
        $this->permissionRole($allPermissions, 'employee', $companyId);

        // === ADMIN ROLE (Full Access) ===
        $this->setupAdminRole($allPermissions, $companyId);

        // === CLIENT ROLE ===
        $this->permissionRole($allPermissions, 'client', $companyId);
    }

    /**
     * Setup Admin role with FULL access + immediate sync
     */
    private function setupAdminRole($allPermissions, $companyId)
    {
        $adminRole = Role::with('roleuser', 'roleuser.user.roles')
            ->where('name', 'admin')
            ->where('company_id', $companyId)
            ->first();

        // Clear existing admin permissions
        PermissionRole::where('role_id', $adminRole->id)->delete();

        // Grant FULL access to everything
        $this->rolePermissionInsert($allPermissions, $adminRole->id, 'all');

        // IMMEDIATELY sync for all admin users
        foreach ($adminRole->roleuser as $roleuser) {
            try {
                $roleuser->user->assignUserRolePermission($adminRole->id);
            } catch (\Exception $e) {
                $this->command->warn("      ⚠ Admin sync failed: " . $e->getMessage());
            }
        }

        $this->command->info("      👑 Admin: FULL access granted (" . $allPermissions->count() . " permissions)");
    }

    /**
     * Bulk insert permission-role mappings (chunked for performance)
     */
    public function rolePermissionInsert($allPermissions, $roleId, $permissionType = 'none')
    {
        $data = [];

        foreach ($allPermissions as $permission) {
            $data[] = [
                'permission_id' => $permission->id,
                'role_id' => $roleId,
                'permission_type_id' => $this->permissionTypes[$permissionType],
            ];
        }

        // Insert in chunks of 100 for large datasets
        foreach (array_chunk($data, 100) as $chunk) {
            PermissionRole::insert($chunk);
        }
    }

    /**
     * Setup specific role permissions (Employee/Client) with overrides
     */
    public function permissionRole($allPermissions, $type, $companyId)
    {
        $role = Role::with('roleuser', 'roleuser.user.roles')
            ->where('name', $type)
            ->where('company_id', $companyId)
            ->first();

        // Clear existing permissions
        PermissionRole::where('role_id', $role->id)->delete();

        // Set ALL to 'none' first
        $this->rolePermissionInsert($allPermissions, $role->id);

        // Get role-specific permission overrides
        $permissionArray = $type === 'client' 
            ? PermissionRole::clientRolePermissions()
            : PermissionRole::employeeRolePermissions();

        $permissionKeys = array_keys($permissionArray);
        $permissions = Permission::whereIn('name', $permissionKeys)->get();

        // Apply specific permissions
        foreach ($permissions as $permission) {
            PermissionRole::where('permission_id', $permission->id)
                ->where('role_id', $role->id)
                ->update([
                    'permission_type_id' => $permissionArray[$permission->name]
                ]);
        }

        // Sync permissions for clients only (employees via cron)
        if ($type === 'client') {
            foreach ($role->roleuser as $roleuser) {
                $roleuser->user->assignUserRolePermission($role->id);
            }
        } else {
            // Flag employees for cron sync
            $userIds = $role->roleuser->pluck('user_id');
            User::whereIn('id', $userIds)->update(['permission_sync' => 0]);
        }

        $this->command->info("      👤 {$type}: " . count($permissionArray) . " permissions configured");
    }
}