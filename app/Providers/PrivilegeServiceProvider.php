<?php

namespace NEUQOJ\Providers;

use Illuminate\Support\ServiceProvider;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Services\PrivilegeService;

class PrivilegeServiceProvider extends ServiceProvider
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
        $this->app->bind('privilege',function (){
            $privilege_server = new PrivilegeService();
            return $privilege_server;
        });
    }
}
