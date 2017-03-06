<?php

namespace NEUQOJ\Providers;

use Illuminate\Support\ServiceProvider;
use NEUQOJ\Services\UserService;

class UserServiceProvider extends ServiceProvider
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
        $this->app->singleton('UserService',function($app){
            return new UserService();
        });
    }
}
