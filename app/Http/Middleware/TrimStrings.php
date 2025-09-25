<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * Laravel automatically trims all incoming string inputs.
     * This array defines exceptions where trimming should not occur.
     *
     * @var array
     */
    protected $except = [
        'password',               // Passwords should not be trimmed
        'password_confirmation',  // Password confirmation should not be trimmed
        'thousand_separator',     // Input for thousand separator should remain as is
        'decimal_separator',      // Input for decimal separator should remain as is
    ];
}
