<?php

namespace NEUQOJ\Providers;

use Illuminate\Support\ServiceProvider;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Services\UserGroupService;

class UserGroupServiceProvider extends ServiceProvider
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
        return $this->app->singleton('UserGroupService',function($app){
            return new UserGroupService();
        });
    }
}
