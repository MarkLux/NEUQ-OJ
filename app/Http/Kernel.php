<?php

namespace NEUQOJ\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use NEUQOJ\Http\Middleware\FlowControllerMiddleware;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \NEUQOJ\Http\Middleware\EnableCrossRequestMiddleware::class,
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,

    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \NEUQOJ\Http\Middleware\EnableCrossRequestMiddleware::class,
            \NEUQOJ\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
//            \NEUQOJ\Http\Middleware\VerifyCsrfToken::class,
        ],

        'api' => [
            'throttle:60,1',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \NEUQOJ\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'can' => \Illuminate\Foundation\Http\Middleware\Authorize::class,
        'guest' => \NEUQOJ\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'flow' => FlowControllerMiddleware::class,
        'token' => \NEUQOJ\Http\Middleware\TokenMiddleware::class,
        'privilege' => \NEUQOJ\Http\Middleware\PrivilegeMiddleware::class,
        'admin' => \NEUQOJ\Http\Middleware\AdminMiddleware::class,
        'user' => \NEUQOJ\Http\Middleware\UserMiddleware::class,
        'bucket' => \NEUQOJ\Http\Middleware\TokenBucketMiddleware::class
    ];
}
