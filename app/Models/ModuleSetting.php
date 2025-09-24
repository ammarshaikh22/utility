<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use App\Traits\HasCompany;

/**
 * App\Models\ModuleSetting
 *
 * This model manages the availability and status of different modules
 * (projects, tickets, invoices, etc.) for a company. It allows checking
 * if a module is active for a specific role (admin, client, employee)
 * and supports assigning modules to companies and roles dynamically.
 *
 * @property int $id Unique identifier for the module setting
 * @property string $module_name Name of the module (e.g., 'projects', 'invoices')
 * @property string $status Status of the module ('active', 'inactive')
 * @property string $type User type for which this module is enabled ('admin', 'client', 'employee')
 * @property int|null $company_id ID of the company this setting belongs to
 * @property \Illuminate\Support\Carbon|null $created_at Record creation timestamp
 * @property \Illuminate\Support\Carbon|null $updated_at Last updated timestamp
 *
 * @property-read \App\Models\Company|null $company Related company via HasCompany trait
 * @property-read mixed $icon Optional accessor for UI representation (if defined elsewhere)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereModuleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ModuleSetting extends BaseModel
{
    use HasCompany;

    /**
     * Client-specific modules that can be enabled.
     */
    const CLIENT_MODULES = [
        'projects',
        'tickets',
        'invoices',
        'estimates',
        'events',
        'messages',
        'tasks',
        'timelogs',
        'contracts',
        'notices',
        'payments',
        'orders',
        'knowledgebase',
    ];

    /**
     * Other modules available for internal use (employees/admins).
     */
    const OTHER_MODULES = [
        'clients',
        'employees',
        'attendance',
        'expenses',
        'leaves',
        'leads',
        'holidays',
        'products',
        'reports',
        'settings',
        'bankaccount'
    ];

    protected $guarded = ['id'];

    /**
     * Check if a module is active for the current user's role.
     *
     * @param string $moduleName The module name (e.g., 'projects')
     * @return bool True if active, false otherwise
     */
    public static function checkModule($moduleName)
    {
        $module = ModuleSetting::where('module_name', $moduleName);

        // Filter based on current user role
        if (in_array('admin', user_roles())) {
            $module = $module->where('type', 'admin');
        } elseif (in_array('client', user_roles())) {
            $module = $module->where('type', 'client');
        } elseif (in_array('employee', user_roles())) {
            $module = $module->where('type', 'employee');
        }

        $module = $module->where('status', 'active')->first();

        return (bool) $module;
    }

    /**
     * Attach company_id to existing modules that currently have NULL.
     *
     * This is mostly used during migrations or initial setup
     * to ensure modules are assigned to the correct company.
     */
    public static function addCompanyIdToNullModule($company, $module)
    {
        if ($company->id == 1) {
            ModuleSetting::withoutGlobalScope(CompanyScope::class)
                ->where('module_name', $module)
                ->whereNull('company_id')
                ->update(['company_id' => $company->id]);
        }
    }

    /**
     * Create module settings for given roles if not already present.
     * Also syncs with the package modules and inserts role permissions.
     *
     * @param string $module Module name
     * @param array $roles Roles to assign (e.g., ['admin', 'client'])
     * @param \App\Models\Company $company The company instance
     */
    public static function createRoleSettingEntry($module, $roles, $company)
    {
        // Ensure company_id is set on existing module if missing
        self::addCompanyIdToNullModule($company, $module);

        $moduleInPackage = collect(json_decode($company->package->module_in_package));

        foreach ($roles as $role) {
            $data = ModuleSetting::withoutGlobalScope(CompanyScope::class)
                ->where('module_name', $module)
                ->where('type', $role)
                ->where('company_id', $company->id)
                ->first();

            // Create entry if it doesn’t exist
            if (!$data) {
                ModuleSetting::create([
                    'module_name'   => $module,
                    'type'          => $role,
                    'company_id'    => $company->id,
                    'status'        => 'active',
                    'is_allowed'    => $moduleInPackage->contains($module) ? 1 : 0,
                ]);
            }
        }

        // Insert role permissions for this module
        PermissionRole::insertModuleRolePermission($module, $company->id);
    }
}
