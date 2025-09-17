<?php

namespace App\Services;

use App\Traits\GoogleOAuth;
use Exception;
use Google_Client;

class Google
{
    use GoogleOAuth;

    protected $client;

    /**
     * Initialize the Google Client with OAuth configuration.
     */
    public function __construct()
    {
        // Set Google OAuth configuration from the GoogleOAuth trait
        $this->setGoogleoAuthConfig();

        // Create a new Google Client instance
        $client = new Google_Client();
        // Configure the client with credentials and settings from the config file
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->setScopes(config('services.google.scopes'));
        $client->setApprovalPrompt(config('services.google.approval_prompt'));
        $client->setAccessType(config('services.google.access_type'));
        $client->setIncludeGrantedScopes(config('services.google.include_granted_scopes'));
        // Set the state parameter to the Google Auth route
        $client->setState(route('googleAuth'));
        $this->client = $client;
    }

    /**
     * Set the access token for the Google Client.
     *
     * @param string $token
     * @return $this
     */
    public function connectUsing($token)
    {
        // Set the access token for the Google Client
        $this->client->setAccessToken($token);

        return $this;
    }

    /**
     * Revoke the specified or current access token.
     *
     * @param string|null $token
     * @return bool
     */
    public function revokeToken($token = null)
    {
        // Use the provided token or the client's current access token
        $token = $token ?? $this->client->getAccessToken();

        // Revoke the token using the Google Client
        return $this->client->revokeToken($token);
    }

    /**
     * Create and return a Google service instance.
     *
     * @param string $service
     * @return mixed
     */
    public function service($service)
    {
        // Dynamically create a Google service class based on the provided service name
        $classname = 'Google_Service_' . $service;

        return new $classname($this->client);
    }

    /**
     * Magic method to call methods on the Google Client dynamically.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $args)
    {
        // Check if the method exists on the Google Client
        if (!method_exists($this->client, $method)) {
            throw new Exception('Call to undefined method ' . $method);
        }

        // Call the method on the Google Client with provided arguments
        return call_user_func_array([$this->client, $method], $args);
    }
}   