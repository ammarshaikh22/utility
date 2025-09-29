<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Setting this to '*' trusts all proxies. This is useful if your application
     * is behind a load balancer or reverse proxy, ensuring correct handling
     * of HTTPS and client IP addresses.
     *
     * @var array
     */
    protected $proxies = '*';
}
