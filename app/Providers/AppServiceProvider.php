<?php

namespace App\Providers;

use App\Models\Company;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services and configurations.
     * This method is used to bind services into the container and set up initial configurations,
     * such as ignoring migrations for Cashier and Sanctum, and forcing HTTPS if configured.
     *
     * @return void
     */
    public function register()
    {
        Cashier::ignoreMigrations();
        Sanctum::ignoreMigrations();

        if (config('app.redirect_https')) {
            $this->app['request']->server->set('HTTPS', true);
        }
    }

    /**
     * Bootstrap any application services.
     * This method is called after all service providers have been registered,
     * allowing for additional setup such as configuring Cashier, forcing HTTPS,
     * setting default string length, registering development tools, and defining macros.
     *
     * @return void
     */
    public function boot()
    {
        Cashier::useCustomerModel(Company::class);

        if (config('app.redirect_https')) {
            URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        if (app()->environment('development')) {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        /**
         * Define a macro for CarbonInterval to format time durations in a human-readable format.
         * This macro allows converting total minutes (or seconds) into a concise, human-friendly string.
         *
         * @param int $totalMinutes The total time in minutes (or seconds if $seconds is true).
         * @param bool $seconds Whether the input is in seconds instead of minutes.
         * @return string The human-readable formatted duration.
         */
        CarbonInterval::macro('formatHuman', function ($totalMinutes, $seconds = false): string {
            if ($seconds) {
                return static::seconds($totalMinutes)->cascade()->forHumans(['short' => true, 'options' => 0]);
                /** @phpstan-ignore-line */
            }

            return static::minutes($totalMinutes)->cascade()->forHumans(['short' => true, 'options' => 0]);
            /** @phpstan-ignore-line */
        });

        // Model::preventLazyLoading(app()->environment('development'));
    }
}