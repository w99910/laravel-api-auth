<?php

namespace Zlt\LaravelApiAuth;

use Illuminate\Support\ServiceProvider;

class LaravelApiAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $config = config('laravel-api-auth') ?? require_once __DIR__ . '/../config/laravel-api-auth.php';
        if ($config['shouldIncludeRoutes']) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/guest.php');
        }
        $this->publishes([
            __DIR__ . '/../config/laravel-api-auth.php' => config_path('laravel-api-auth.php'),
        ]);
    }

    public function register()
    {
    }
}
