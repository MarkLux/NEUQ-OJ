<?php

namespace NEUQOJ\Http\Middleware;

use Closure;
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
    public function handle($request, Closure $next,TokenRepository $tokenRepository,UserRepository $userRepository)
    {
        if(!$request->hasHeader('token'))
            throw new NeedLoginException();

        $tokenStr = $request->header('token');
        $token = $tokenRepository->getBy('token',$tokenStr)->first();
        if($token == null)
            throw new NeedLoginException();

        if($token->expires_at < time())
            throw new TokenExpireException();
        $user = $userRepository->get($token->user_id);

        $request->user = $user;
        return $next($request);
    }
}
