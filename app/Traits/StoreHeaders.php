<?php

namespace App\Traits;

use DeviceDetector\Parser\Client\Browser;
use Exception;

/**
 * Trait StoreHeaders
 *
 * Captures and stores request headers, IP address, and location details
 * into the given model. Useful for logging user environment information.
 */
trait StoreHeaders
{
    /**
     * Store headers, IP address, and location details on the given model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The model to attach request metadata to.
     * @return void
     */
    public function storeHeaders($model): void
    {
        // Localhost IPs that should be ignored for location tracking
        $whitelist = [
            '127.0.0.1',
            '::1',
        ];

        try {
            // Ensure Browser class exists before using
            if (class_exists(Browser::class)) {

                // Store browser/device headers as JSON
                $model->headers = json_encode(
                    \Browser::detect()->toArray(),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                );

                // Only process IP and location if not in whitelist
                if (!in_array(request()->ip(), $whitelist)) {
                    $model->register_ip = request()->ip();

                    // Check if GeoLite2 database exists
                    if (file_exists(database_path('maxmind/GeoLite2-City.mmdb'))) {
                        // Try to fetch location details using IP
                        if ($position = \Stevebauman\Location\Facades\Location::get(request()->ip())) {
                            $model->location_details = json_encode(
                                $position,
                                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                            );
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Fail silently to prevent breaking main request flow
            // You could log this if debugging is needed.
            // logger()->error('Header storage failed: '.$e->getMessage());
        }
    }
}
