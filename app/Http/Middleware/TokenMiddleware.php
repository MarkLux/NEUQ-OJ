<?php

namespace NEUQOJ\Http\Middleware;

use Closure;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\UserLockedException;
use NEUQOJ\Exceptions\UserNotActivatedException;
use NEUQOJ\Repository\Eloquent\TokenRepository;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Exceptions\NeedLoginException;
use NEUQOJ\Exceptions\TokenExpireException;


class TokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
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

        if(!$request->hasHeader('token'))
            throw new NeedLoginException();

        $tokenStr = $request->header('token');

        $token = $this->tokenRepository->getBy('token',$tokenStr)->first();

        if($token === null)
            throw new NeedLoginException();

        if($token->expires_at < $time)
            throw new TokenExpireException();
        $user = $this->userRepository->get($token->user_id)->first();

        if($user->status == -1)
            throw new UserLockedException();
        elseif($user->status == 0)
            throw new UserNotActivatedException();

        $request->user = $user;
        return $next($request);
    }
}
