<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Define the policy mappings for the application.
     * This array maps Eloquent models to their corresponding policy classes for authorization.
     *
     * @var array
     */
    protected $policies = [
        /* 'App\Models\Model' => 'App\Policies\ModelPolicy', */
    ];

    /**
     * Register authentication and authorization services.
     * This method is used to register the application's policies for authorization.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}