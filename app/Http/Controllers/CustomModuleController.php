<?php

namespace App\Http\Controllers;

use App\Events\ModuleStatusChanged;
use App\Helper\Reply;
use App\Models\ModuleSetting;
use App\Models\GlobalSetting;
use Froiden\Envato\Functions\EnvatoUpdate;
use Froiden\Envato\Traits\ModuleVerify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Macellan\Zip\Zip;
use Nwidart\Modules\Facades\Module;

class CustomModuleController extends AccountBaseController
{
    use ModuleVerify;

    /**
     * Constructor for the CustomModuleController.
     * Initializes the parent controller, sets the page title and active setting menu, and applies middleware to restrict access to super admins with custom module management permissions.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.moduleSettings';
        $this->activeSettingMenu = 'module_settings';
        $this->middleware(function ($request, $next) {
            // Restrict access to super admins with permission to manage custom module settings
            abort_403(GlobalSetting::validateSuperAdmin('manage_superadmin_custom_module_settings'));

            return $next($request);
        });
    }

    /**
     * Displays the custom module settings index page.
     * Retrieves all custom modules, filters out the UniversalBundle, and renders the index view.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->type = 'custom';
        $this->updateFilePath = config('froiden_envato.tmp_path');

        // Fetch all modules, excluding UniversalBundle
        $this->allModules = Module::toCollection()->filter(function ($module, $key) {
            return $key !== 'UniversalBundle';
        });

        // Fetch the UniversalBundle module separately
        $this->universalBundle = Module::find('UniversalBundle');

        $this->view = 'custom-modules.ajax.custom';
        $this->activeTab = 'custom';
        $this->plugins = collect(EnvatoUpdate::plugins());

        // Handle AJAX requests by rendering the custom module view
        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        // Render the main module settings index view
        return view('module-settings.index', $this->data);
    }

    /**
     * Displays the form for installing a new custom module.
     * Sets the page title and type, and renders the install view.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->pageTitle = 'app.menu.moduleSettingsInstall';
        $this->type = 'custom';
        $this->updateFilePath = config('froiden_envato.tmp_path');

        // Render the install module view
        return view('custom-modules.install', $this->data);
    }

    /**
     * Stores a new custom module by processing an uploaded zip file.
     * Validates the PHP-ZIP extension, extracts the module, validates compatibility, and installs it if valid.
     *
     * @param Request $request The request containing the module zip file path.
     * @return array JSON response with success or error message.
     * @throws \Exception
     */
    public function store(Request $request)
    {
        // Check if PHP-ZIP extension is available
        if (!extension_loaded('zip')) {
            return Reply::error('<b>PHP-ZIP</b> extension is missing on your server. Please install the extension.');
        }

        // Indicate installation completion
        File::put(public_path() . '/install-version.txt', 'complete');

        $filePath = $request->filePath;
        $zip = Zip::open($filePath);
        $zipName = $this->getZipName($filePath);

        // Handle codecanyon zip files
        if (str_contains($zipName, 'codecanyon-')) {
            $zipName = $this->unzipCodecanyon($zip);
        } else {
            $zip->extract(storage_path('app') . '/Modules');
        }

        $moduleName = str_replace('.zip', '', $zipName);

        // Validate the module
        $validateModule = $this->validateModule($moduleName);

        if ($validateModule['status'] == true) {
            // Move module files to the Modules directory
            File::moveDirectory(storage_path('app') . '/Modules/' . $moduleName, base_path() . '/Modules/' . $moduleName, true);

            // Clear module cache
            cache()->forget('laravel-modules');

            // Delete temporary Modules directory
            File::deleteDirectory(storage_path('app') . '/Modules/');

            // Update module version if enabled
            if (module_enabled($moduleName)) {
                $this->updateVersion($moduleName);
            }

            // Handle UniversalBundle module activation
            if ($moduleName == 'UniversalBundle') {
                $module = Module::findOrFail($moduleName);
                $module->enable();
                Artisan::call('module:migrate', [$moduleName, '--force' => true]);
                event(new ModuleStatusChanged($module, 'active'));
            }

            // Clear cache and session data
            $this->flushData();

            // Return success response
            return Reply::success('Installed successfully.');
        }

        // Return error response if validation fails
        return Reply::error($validateModule['message']);
    }

    /**
     * Validates a module for compatibility with the application.
     * Checks for PHP-ZIP extension, module configuration, and compatibility with the application's version and product.
     *
     * @param string $moduleName The name of the module to validate.
     * @return array Validation result with status and message.
     */
    public function validateModule($moduleName)
    {
        $appName = str_replace('-new', '', config('froiden_envato.envato_product_name'));
        $wrongMessage = 'The zip that you are trying to install is not compatible with ' . $appName . ' version';

        // Check for PHP-ZIP extension
        if (!extension_loaded('zip')) {
            return [
                'status' => false,
                'message' => '<b>PHP-ZIP</b> extension is missing on your server. Please install the extension.'
            ];
        }

        $configPath = storage_path('app') . '/Modules/' . $moduleName . '/Config/config.php';

        // Check if module configuration file exists
        if (!file_exists($configPath)) {
            return [
                'status' => false,
                'message' => $wrongMessage
            ];
        }

        $config = require_once $configPath;

        // Check parent_envato_id compatibility
        if (!isset($config['parent_envato_id']) || $config['parent_envato_id'] !== config('froiden_envato.envato_item_id')) {
            return [
                'status' => false,
                'message' => 'You are installing the wrong module for this product'
            ];
        }

        // Check parent_envato_id again for redundancy
        if ($config['parent_envato_id'] !== config('froiden_envato.envato_item_id')) {
            return [
                'status' => false,
                'message' => 'You are installing wrong module for this product'
            ];
        }

        // Check if parent_min_version is defined
        if (!isset($config['parent_min_version'])) {
            $errorMessage = App::environment('codecanyon') ? 'Please download and install the latest version of the module.' : 'Minimum version of <b>' . $appName . ' main application</b> is not defined in the Module.';
            return [
                'status' => false,
                'message' => $errorMessage
            ];
        }

        // Check application version compatibility
        if ($config['parent_min_version'] >= File::get('version.txt')) {
            return [
                'status' => false,
                'message' => 'Minimum version of <b>' . $appName . ' main application</b> should be greater than or equal to <b>' . $config['parent_min_version'] . '</b>. Your application version is <b>' . File::get('version.txt') . '</b>'
            ];
        }

        // Check parent_product_name compatibility
        if (!isset($config['parent_product_name']) || $config['parent_product_name'] !== config('froiden_envato.envato_product_name')) {
            return [
                'status' => false,
                'message' => $wrongMessage
            ];
        }

        return [
            'status' => true,
            'message' => 'Unzipped successfully'
        ];
    }

    /**
     * Clears cache, session data, and re-authenticates the user.
     * Used after module installation or status changes to ensure a clean state.
     */
    private function flushData()
    {
        Artisan::call('optimize:clear');
        Artisan::call('view:clear');
        $user = auth()->id();
        // Clear cache and session
        cache()->flush();
        session()->flush();
        auth()->logout();
        // Re-authenticate the user
        auth()->loginUsingId($user);
    }

    /**
     * Displays the module purchase verification page.
     * Delegates to the verifyModulePurchase method from the ModuleVerify trait.
     *
     * @param int $id The module ID.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        return $this->verifyModulePurchase($id);
    }

    /**
     * Updates the status of a module (enable/disable).
     * Updates module settings, runs migrations if enabled, and clears cache/session data.
     *
     * @param Request $request The request containing the module status.
     * @param string $moduleName The name of the module to update.
     * @return array JSON response with success message and redirect URL.
     */
    public function update(Request $request, $moduleName)
    {
        $module = Module::findOrFail($moduleName);
        $status = $request->status;

        // Delete existing module settings
        ModuleSetting::where('module_name', $moduleName)->delete();

        // Enable or disable the module
        ($status == 'active') ? $module->enable() : $module->disable();

        // Trigger module status change event
        event(new ModuleStatusChanged($moduleName, $status));

        // Register the module
        $module->register();

        $plugins = \Nwidart\Modules\Facades\Module::allEnabled();

        // Run migrations for active modules
        if ($status == 'active') {
            $this->runModuleMigrateCommand($moduleName);
            $this->runActivateCommand(strtolower($moduleName));
        }

        // Clear cache and session data
        $this->flushData();

        // Handle specific module activations (e.g., subdomain)
        if (strtolower($moduleName) == 'subdomain' && ($status == 'active')) {
            \session(['subdomain_module_activated' => true]);
        }

        // Update cached user modules and plugins
        cache()->forget('user_modules');
        cache(['worksuite_plugins' => array_keys($plugins)]);

        // Handle languagepack module activation
        if (strtolower($moduleName) == 'languagepack' && $status == 'active') {
            session(['languagepack_module_activated' => true]);
        }

        // Return success response with redirect
        return Reply::redirect(route('custom-modules.index') . '?tab=custom', 'Status Changed. Reloading');
    }

    /**
     * Verifies the purchase code for a module.
     * Validates the purchase code and delegates to the modulePurchaseVerified method from the ModuleVerify trait.
     *
     * @param Request $request The request containing the purchase code and module name.
     * @return mixed Result from module purchase verification.
     */
    public function verifyingModulePurchase(Request $request)
    {
        $request->validate([
            'purchase_code' => 'required|max:80',
        ]);

        $module = $request->module;
        $purchaseCode = $request->purchase_code;

        return $this->modulePurchaseVerified($module, $purchaseCode);
    }

    /**
     * Extracts a codecanyon zip file to retrieve the module zip.
     * Processes nested zip files within codecanyon packages.
     *
     * @param Zip $zip The zip file object.
     * @return string|bool The name of the extracted module zip or false if not found.
     * @throws \Exception
     */
    private function unzipCodecanyon($zip)
    {
        $codeCanyonPath = storage_path('app') . '/Modules/Codecanyon';
        $zip->extract($codeCanyonPath);
        $files = File::allFiles($codeCanyonPath);

        foreach ($files as $file) {
            if (str_contains($file->getRelativePathname(), '.zip')) {
                $filePath = $file->getRelativePathname();
                $zip = Zip::open($codeCanyonPath . '/' . $filePath);
                $zip->extract(storage_path('app') . '/Modules');
                return $this->getZipName($filePath);
            }
        }

        return false;
    }

    /**
     * Extracts the zip file name from the file path.
     *
     * @param string $filePath The path to the zip file.
     * @return string The name of the zip file.
     */
    private function getZipName($filePath)
    {
        $array = explode('/', str_replace('\\', '/', $filePath));
        return end($array);
    }

    /**
     * Updates the version of a module.
     * Verifies the purchase code if available in the module's settings.
     *
     * @param string $moduleName The name of the module.
     */
    private function updateVersion($moduleName)
    {
        try {
            $config = require base_path() . '/Modules/' . $moduleName . '/Config/config.php';
            $setting = (new $config['setting'])::first();

            // Verify purchase code if it exists
            if ($setting?->purchase_code) {
                $this->modulePurchaseVerified(strtolower($moduleName), $setting->purchase_code);
            }
        } catch (\Exception $e) {
            logger($e->getMessage());
        }
    }

    /**
     * Runs the migration command for a module.
     *
     * @param string $moduleName The name of the module.
     */
    private function runModuleMigrateCommand($moduleName)
    {
        Artisan::call('module:migrate', [$moduleName, '--force' => true]);
    }

    /**
     * Runs the module-specific activation command if it exists.
     *
     * @param string $moduleName The name of the module.
     */
    private function runActivateCommand($moduleName)
    {
        $command = $moduleName . ':activate';
        $artisanCommands = \Artisan::all();

        if (array_has($artisanCommands, $command)) {
            Artisan::call($command);
        }
    }
}