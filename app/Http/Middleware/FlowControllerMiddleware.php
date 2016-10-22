<?php

namespace NEUQOJ\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use League\Flysystem\Exception;
use NEUQOJ\Common\FlowToken;
use NEUQOJ\Common\RedisHelper;
use NEUQOJ\Common\Utils;

class FlowControllerMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $cost = 1)
    {
        if(!env('IS_REDIS', false)){
            return $next($request);
        }
        $userId = 1;
        $cache = RedisHelper::command('Hmget', 'userId' . $userId, [
            'token',
            'time'
        ]);

        if (!isset($cache['token']) || !isset($cache['time'])) {
            $cache = [
                'token' => FlowToken::DEFAULT_TOKEN,
                'time' => Utils::createTimeStamp()
            ];
        }

        list($isAllow, $current) = FlowToken::isAllow($cost, $cache['token'], $cache['time']);

        if($isAllow){
            RedisHelper::command('Hmset', 'userId' . $userId, [
                'token', $current, 'time', Utils::createTimeStamp()
            ], false);
        }else{
            abort(400, '请求过多');
        }
        return $next($request);
    }
}
