<?php

namespace Zlt\LaravelApiAuth;

use Illuminate\Support\ServiceProvider;
use Zlt\LaravelApiAuth\Middlewares\AddJsonHeader;

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

        if ($config['addApplicationJsonHeader']) {
            $this->app['router']->prependMiddlewareToGroup('api', AddJsonHeader::class);
        }
    }

    public function register()
    {
    }
}
