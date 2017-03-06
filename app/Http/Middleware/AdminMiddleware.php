<?php

namespace NEUQOJ\Http\Middleware;

use Closure;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Services\RoleService;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function handle($request, Closure $next)
    {
        $user = $request->user;
        if(!$this->roleService->hasRole($user['id'],'admin'))
            throw new NoPermissionException();
        else
            return $next($request);
    }
}
