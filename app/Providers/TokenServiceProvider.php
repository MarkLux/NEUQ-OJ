<?php

namespace NEUQOJ\Providers;

use Illuminate\Support\ServiceProvider;
use NEUQOJ\Services\TokenService;

class TokenServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('TokenService',function ($app){
            return new TokenService();
        });
    }
}