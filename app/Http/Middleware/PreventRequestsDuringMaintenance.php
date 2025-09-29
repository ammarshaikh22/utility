<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * This array allows you to define exceptions so that certain routes
     * remain accessible even when the application is in maintenance mode.
     *
     * @var array
     */
    protected $except = [
        // Add route URIs here that should bypass maintenance mode
    ];
}
