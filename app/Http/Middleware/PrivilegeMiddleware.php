<?php

namespace NEUQOJ\Http\Middleware;

use Closure;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\PrivilegeNotExistException;
use NEUQOJ\Repository\Eloquent\PrivilegeRepository;
use NEUQOJ\Repository\Eloquent\UsrPriRepository;

class PrivilegeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    protected $priRepo;
    protected $usrPriRepo;

    public function __construct(PrivilegeRepository $privilegeRepository,UsrPriRepository $usrPriRepository)
    {
        $this->priRepo = $privilegeRepository;
        $this->usrPriRepo = $usrPriRepository;
    }

    public function handle($request, Closure $next, ... $params)
    {
        foreach ($params as $priStr) {
            //TODO 查询操作放在for循环外
            $privilege = $this->priRepo->getBy('name', $priStr)->first();

            if($privilege == null)
                throw new PrivilegeNotExistException();

            $result = $this->usrPriRepo->getByMult([
                'user_id' => $request->user->id,
                'privilege_id' => $privilege->id
            ])->first();

            if ($result == null)
                throw new NoPermissionException();
        }

        return $next($request);
    }
}
