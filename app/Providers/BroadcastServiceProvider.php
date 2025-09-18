<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register broadcasting services.
     * This method retrieves Pusher settings from the database and configures the broadcasting
     * driver and Pusher connection settings dynamically, except in demo or development environments.
     * Exceptions are caught and ignored to prevent issues during registration.
     *
     * @return void
     */
    public function register()
    {
        try {
            $pusherSetting = DB::table('pusher_settings')->first();

            if ($pusherSetting) {
                if (!in_array(config('app.env'), ['demo', 'development'])) {
                    $driver = ($pusherSetting->status == 1) ? 'pusher' : 'null';

                    Config::set('broadcasting.default', $driver);
                    Config::set('broadcasting.connections.pusher.key', $pusherSetting->pusher_app_key);
                    Config::set('broadcasting.connections.pusher.secret', $pusherSetting->pusher_app_secret);
                    Config::set('broadcasting.connections.pusher.app_id', $pusherSetting->pusher_app_id);
                    Config::set('broadcasting.connections.pusher.options.host', 'api-'.$pusherSetting->pusher_cluster.'.pusher.com');
                }
            }
        }
        // @codingStandardsIgnoreLine
        catch (\Exception $e) {
        } // phpcs:ignore
    }

    /**
     * Bootstrap broadcasting services.
     * This method sets up broadcasting routes and includes the channels.php file
     * to define channel authorization logic.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes();

        require base_path('routes/channels.php');
    }
}