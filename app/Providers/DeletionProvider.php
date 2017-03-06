<?php

namespace NEUQOJ\Providers;

use Illuminate\Support\ServiceProvider;

use NEUQOJ\Services\DeletionService;

class DeletionProvider extends ServiceProvider
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
        return $this->app->singleton('DeletionService',function($app){
            return new DeletionService();
        });
    }
}
