<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class TranslateSettingConfigProvider extends ServiceProvider
{
    /**
     * Register translation settings for the application.
     * This method checks for the existence of the translate_settings table,
     * retrieves the Google Translate API key, and sets it in the configuration.
     * Exceptions are caught and ignored to prevent issues during registration.
     *
     * @return void
     */
    public function register()
    {
        try {
            // TODO: To be removed in next update
            if (Schema::hasTable('translate_settings')) {
                $translateSetting = DB::table('translate_settings')->first();

                if ($translateSetting) {
                    Config::set('laravel_google_translate.google_translate_api_key', $translateSetting->google_key);
                }
            }
        }
        // @codingStandardsIgnoreLine
        catch (\Exception $e) {
        }
    }

    /**
     * Bootstrap any application services.
     * This method is currently empty but can be used for additional setup if needed.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}