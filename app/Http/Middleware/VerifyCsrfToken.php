<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * These routes are typically called by external services (like webhooks)
     * and cannot provide a CSRF token. Excluding them ensures the requests
     * are processed without CSRF errors.
     *
     * @var array
     */
    protected $except = [
        '*-webhook/*',                 // Any route ending with -webhook
        '*_webhook/*',                 // Any route ending with _webhook
        '*_webhook',                    // Any route named *_webhook
        '*-webhook',                    // Any route named *-webhook
        '/lead-form/leadStore',         // Lead form store endpoint
        '/lead-form/ticket-store',      // Lead form ticket endpoint
        '*/iclock/*',                   // iClock related endpoints
        '/billing-verify-webhook/*',    // Billing webhook verification
        '*/payfast-notification/*'      // PayFast payment notifications
    ];
}
