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
        $collection = $this->priRepo->getIn('name',$params);

        $privileges = [];

        if(count($collection)!=count($params))
            throw new PrivilegeNotExistException();

        //重新压缩数组
        foreach ($collection as $item){
            $privileges[] = $item->id;
        }

        $collection = $this->usrPriRepo->getRes($request->user->id,$privileges);

        if(count($collection)!=count($params))
            throw new NoPermissionException();

        return $next($request);
    }
}
