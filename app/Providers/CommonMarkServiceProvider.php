<?php

namespace NEUQOJ\Providers;

use Illuminate\Support\ServiceProvider;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;

class CommonMarkServiceProvider extends ServiceProvider
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
        $this->app->singleton('CommonMarkService',function($app){
            $environment = \League\CommonMark\Environment::createCommonMarkEnvironment();
            $config = ['html' => 'escape'];
            return new \League\CommonMark\CommonMarkConverter($config,$environment);
        });
    }
}
