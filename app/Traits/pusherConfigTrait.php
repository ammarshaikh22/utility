<?php

namespace App\Traits;

use Pusher\Pusher;

trait pusherConfigTrait
{
    /**
     * Trigger a Pusher event on a given channel.
     *
     * @param string $channel  The name of the channel to publish to.
     * @param string $event    The event name to trigger.
     * @param array  $data     The payload data to send with the event.
     *
     * @throws \Pusher\PusherException If something goes wrong with Pusher.
     * @return void
     */
    public function triggerPusher($channel, $event, $data): void
    {
        // Fetch saved Pusher configuration (e.g., from database or config).
        $pusherSetting = pusher_settings();

        // Only proceed if Pusher is enabled in settings.
        if ($pusherSetting->status) {

            // Create a new Pusher client instance with app credentials.
            $pusher = new Pusher(
                $pusherSetting->pusher_app_key,
                $pusherSetting->pusher_app_secret,
                $pusherSetting->pusher_app_id,
                [
                    'cluster' => $pusherSetting->pusher_cluster, // Which Pusher cluster to use.
                    'useTLS' => $pusherSetting->force_tls       // Force TLS if enabled.
                ]
            );

            // Trigger the event on the specified channel with provided data.
            $pusher->trigger($channel, $event, $data);
        }
    }
}
