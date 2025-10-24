<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Carbon\Carbon;
use Froiden\Envato\Traits\AppBoot;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, AppBoot;

    /**
     * An array to hold data that can be passed to views.
     *
     * @var array
     */
    public $data = [];

    /**
     * Magic method to dynamically set properties and store them in the $data array.
     * Allows setting view data via object notation.
     *
     * @param mixed $name The property name.
     * @param mixed $value The value to assign.
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Magic method to dynamically get properties from the $data array.
     * Allows accessing view data via object notation.
     *
     * @param mixed $name The property name.
     * @return mixed The value from the $data array.
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * Magic method to check if a property exists in the $data array.
     * Allows isset() checks on dynamic properties.
     *
     * @param mixed $name The property name.
     * @return bool True if the property exists in $data.
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Constructor for the Controller.
     * Applies middleware to handle global setup, such as checking migration status, loading settings, configuring the app locale, and environment-specific configurations.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Check migration status
            $this->checkMigrateStatus();

            // Load GDPR and global settings
            $this->gdpr = gdpr_setting();
            $this->global = global_setting();

            // Load company or global settings (WORKSUITESAAS)
            $this->company = companyOrGlobalSetting();

            // Load social authentication settings
            $this->socialAuthSettings = social_auth_setting();

            // Set company name and app name based on company or global settings
            $this->companyName = company() ? $this->company->company_name : $this->global->global_app_name;
            $this->appName = company() ? $this->company->app_name : $this->global->global_app_name;

            // Set locale from session, company, or global settings
            $this->locale = session('locale') ? session('locale') : (company() ? $this->company->locale : $this->global->locale);

            // Set task board column length from company settings
            $this->taskBoardColumnLength = $this->company ? $this->company->taskboard_length : 10;

            // Configure app name and URL
            config(['app.name' => $this->companyName]);
            config(['app.url' => url('/')]);

            // Set application and Carbon locale
            App::setLocale($this->locale);
            Carbon::setLocale($this->locale);

            // Set locale for time formatting
            setlocale(LC_TIME, $this->locale . '_' . mb_strtoupper($this->locale));

            // Set debug mode for codecanyon environment
            if (config('app.env') == 'codecanyon') {
                config(['app.debug' => $this->global->app_debug]);
            }

            // Allow user ID in froiden_envato if user is authenticated
            if (user()) {
                config(['froiden_envato.allow_users_id' => true]);
            }

            return $next($request);
        });
    }

    /**
     * Checks the migration status of the application.
     * Calls the check_migrate_status() helper function.
     *
     * @return mixed The result from the migration status check.
     */
    public function checkMigrateStatus()
    {
        return check_migrate_status();
    }

    /**
     * Renders a view for AJAX responses.
     * Renders the specified view with the current data and returns a JSON response with the HTML and page title.
     *
     * @param string $view The view name to render.
     * @return array JSON response with status, HTML, and page title.
     */
    public function returnAjax($view)
    {
        $html = view($view, $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
    }
}