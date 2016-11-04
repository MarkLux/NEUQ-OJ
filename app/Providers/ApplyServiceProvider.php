<?php

namespace NEUQOJ\Providers;

use Illuminate\Support\ServiceProvider;

class ApplyServiceProvider extends ServiceProvider
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
        $this->app->singleton('ApplyService',function($app){
        return new ApplyService();
    });
    }
}
