<?php

namespace Database\Seeders;

/**
 * Core Super Admin Database Seeder - creates global system configuration for super admin
 */

use App\Models\CustomFieldGroup;
use App\Models\GlobalSetting;
use App\Models\Module;
use App\Models\SuperAdmin\GlobalCurrency;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\PackageSetting;
use App\Models\SuperAdmin\StripeSetting;
use App\Models\SuperAdmin\SupportTicketType;
use App\Models\ThemeSetting;
use App\Scopes\CompanyScope;
use Illuminate\Database\Seeder;

class CoreSuperAdminDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->globalCurrency();      // 4 global currencies (USD, GBP, EUR, INR)
        $this->package();             // Default free package
        $this->packageSetting();      // Trial package settings
        $this->stripeSetting();       // Stripe payment gateway (disabled)
        $this->supportTicketType();   // 3 support ticket types
        $this->themeSetting();        // Super admin theme colors
        $this->customFieldGroup();    // Company custom field group
    }

    /**
     * Create 4 global currencies with full formatting settings
     */
    private function globalCurrency()
    {
        $globalCurrency = [
            [
                'currency_name' => 'Dollars',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'exchange_rate' => 1,
                'currency_position' => 'left',        // Symbol before amount
                'no_of_decimal' => 2,                 // 2 decimal places
                'thousand_separator' => ',',          // 1,000
                'decimal_separator' => '.'            // 1,000.00
            ],
            [
                'currency_name' => 'Pounds',
                'currency_symbol' => '£',
                'currency_code' => 'GBP',
                'exchange_rate' => 1,
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.'
            ],
            [
                'currency_name' => 'Euros',
                'currency_symbol' => '€',
                'currency_code' => 'EUR',
                'exchange_rate' => 1,
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.'
            ],
            [
                'currency_name' => 'Rupee',
                'currency_symbol' => '₹',
                'currency_code' => 'INR',
                'exchange_rate' => 1,
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.'
            ],
        ];

        GlobalCurrency::insert($globalCurrency);           // Bulk insert 4 currencies
    }

    /**
     * Create DEFAULT free package (20 employees, all modules)
     */
    private function package()
    {
        // Get all active module names (exclude settings, dashboards, restApi)
        $packageModules = Module::where('module_name', '<>', 'settings')
            ->where('module_name', '<>', 'dashboards')
            ->where('module_name', '<>', 'restApi')
            ->whereNotIn('module_name', Module::disabledModuleArray())
            ->pluck('module_name')
            ->toJson();

        $packages = new Package();
        $packages->name = 'Default';                                    // Package name
        $packages->description = 'Its a default package and cannot be deleted'; // Non-deletable
        $packages->annual_price = 0;                                    // Free
        $packages->monthly_price = 0;                                   // Free
        $packages->max_employees = 20;                                  // 20 employee limit
        $packages->default = 'yes';                                     // Default package
        $packages->is_free = 1;                                         // Free package flag
        $packages->sort = 1;                                            // Display order
        $packages->module_in_package = $packageModules;                 // JSON of included modules
        $packages->save();
    }

    /**
     * Create TRIAL package settings + 30-day trial package
     */
    private function packageSetting()
    {
        // Get all active module names (same as default package)
        $packageModules = Module::where('module_name', '<>', 'settings')
            ->where('module_name', '<>', 'dashboards')
            ->where('module_name', '<>', 'restApi')
            ->whereNotIn('module_name', Module::disabledModuleArray())
            ->pluck('module_name')
            ->toJson();

        // Global trial settings
        $packageSetting = new PackageSetting();
        $packageSetting->status = 'inactive';                           // Trial system disabled
        $packageSetting->trial_message = 'Start 30 days free trial';    // Trial message
        $packageSetting->no_of_days = 30;                               // 30-day trial
        $packageSetting->modules = $packageModules;                     // Included modules
        $packageSetting->save();

        // Get global currency
        $global = GlobalSetting::with('currency')->first();

        // Create TRIAL package
        $packages = new Package();
        $packages->name = 'Trial';
        $packages->currency_id = $global ? $global->currency_id : null; // Use global currency
        $packages->description = 'Its a trial package';
        $packages->max_storage_size = 500;                              // 500MB storage
        $packages->annual_price = 0;                                    // Free
        $packages->monthly_price = 0;                                   // Free
        $packages->max_employees = 20;                                  // 20 employee limit
        $packages->stripe_annual_plan_id = 'trial_plan';                // Stripe plan ID
        $packages->stripe_monthly_plan_id = 'trial_plan';
        $packages->default = 'trial';                                   // Trial default
        $packages->module_in_package = $packageModules;
        $packages->save();
    }

    /**
     * Create Stripe payment gateway settings (disabled)
     */
    private function stripeSetting()
    {
        $stripe = new StripeSetting();
        $stripe->api_key = null;                                        // No API key (disabled)
        $stripe->save();
    }

    /**
     * Create 3 support ticket types
     */
    private function supportTicketType()
    {
        $type = [
            ['type' => 'Question'],         // General questions
            ['type' => 'Problem'],          // Technical issues
            ['type' => 'General Query'],    // Other inquiries
        ];

        SupportTicketType::insert($type);                               // Bulk insert 3 types
    }

    /**
     * Create super admin theme colors (red/black theme)
     */
    private function themeSetting()
    {
        $superadminTheme = ThemeSetting::where('panel', 'superadmin')->first();

        if (!$superadminTheme) {
            $superadminTheme = new ThemeSetting();
            $superadminTheme->panel = 'superadmin';                     // Super admin panel
            $superadminTheme->header_color = '#ed4040';                 // Red header
            $superadminTheme->sidebar_color = '#292929';                // Dark sidebar
            $superadminTheme->sidebar_text_color = '#cbcbcb';           // Light gray text
            $superadminTheme->link_color = '#ffffff';                   // White links
            $superadminTheme->save();
        }
    }

    /**
     * Create Company custom field group (bypassing CompanyScope)
     */
    private function customFieldGroup()
    {
        // Bypass CompanyScope to get global Company group
        $customFieldGroup = CustomFieldGroup::withoutGlobalScope(CompanyScope::class)
            ->where('name', 'Company')
            ->first();

        if ($customFieldGroup) {
            $customFieldGroup->model = 'App\Models\Company';            // Link to Company model
            $customFieldGroup->save();
        } else {
            $customFieldGroup = new CustomFieldGroup();
            $customFieldGroup->name = 'Company';                        // Group name
            $customFieldGroup->model = 'App\Models\Company';            // Link to Company model
            $customFieldGroup->save();
        }
    }
}