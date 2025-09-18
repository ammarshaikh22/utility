<?php

namespace App\View\Components;

use App\Models\GlobalSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\View\Component;

class Auth extends Component
{
    /**
     * Create a new component instance.
     *
     * The constructor doesn't do anything for now but can be used in the future if needed.
     */
    public function __construct()
    {
        // Empty constructor, could be used for initializing properties if needed in the future
    }

    /**
     * Get the view / contents that represent the component.
     *
     * This method handles the logic to get the global settings and language settings based on the current subdomain.
     *
     * @return View|string - The view for this component that represents authentication settings.
     */
    public function render()
    {
        // Check if the 'Subdomain' module is enabled to determine the company settings
        if (module_enabled('Subdomain')) {
            // Retrieve the company settings based on the subdomain
            $company = getCompanyBySubDomain();
            // Use the company settings or fallback to global settings
            $globalSetting = $company ?? GlobalSetting::first();
        } else {
            // Retrieve the global settings if the 'Subdomain' module is not enabled
            $globalSetting = global_setting();
        }

        // Get the available languages from the settings
        $languages = language_setting();

        // Set the app theme and locale based on the global settings
        $appTheme = $globalSetting;

        // Set the locale based on the session or global settings
        App::setLocale(session('locale') ?? $globalSetting->locale);

        // Return the 'auth' component view with the global settings, app theme, and languages
        return view('components.auth', ['globalSetting' => $globalSetting, 'appTheme' => $appTheme, 'languages' => $languages]);
    }
}
