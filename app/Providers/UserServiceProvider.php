<?php

namespace NEUQOJ\Providers;

use Illuminate\Support\ServiceProvider;
use NEUQOJ\Service\UserServer;

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
        $this->app->bind('user', function () {
            $user_server = new UserServer();
            return $user_server;
        });
    }
}
