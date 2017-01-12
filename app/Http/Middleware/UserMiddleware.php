<?php

namespace NEUQOJ\Http\Middleware;

use Closure;
use NEUQOJ\Common\Utils;
use Illuminate\Http\Request;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Repository\Eloquent\TokenRepository;

class UserMiddleware
{
    /*
     *这个中间件和token中间的差别只是当用户不存在的时候不抛异常
     * 用于登陆和不登陆显示有差别的服务S
    */

    protected $userRepository;
    protected $tokenRepository;

    public function __construct(UserRepository $ur,TokenRepository $tr)
    {
        $this->userRepository = $ur;
        $this->tokenRepository = $tr;
    }

    public function handle($request, Closure $next)
    {
        $time = Utils::createTimeStamp();

        if($request->hasHeader('token'))
        {
            $tokenStr = $request->header('token');
            $token = $this->tokenRepository->getBy('token',$tokenStr)->first();
            if($token != null&&$token->expires_at > $time)
            {
                $user = $this->userRepository->get($token->user_id)->first();
                $request->user = $user;
            }
        }

        return $next($request);
    }
}
