<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * Laravel uses this to prevent host header poisoning attacks.
     * By default, it trusts all subdomains of the application's URL.
     *
     * @return array
     */
    public function hosts()
    {
        // Trust all subdomains of the application URL
        return [
            $this->allSubdomainsOfApplicationUrl(),
        ];
    }
}
