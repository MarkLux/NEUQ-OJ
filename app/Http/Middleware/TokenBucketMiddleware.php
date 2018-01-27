<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 2018/1/26
 * Time: 下午10:22
 */

namespace NEUQOJ\Http\Middleware;

use NEUQOJ\Common\TokenBucket;
use Closure;
use NEUQOJ\Exceptions\Other\TooManyTryException;


class TokenBucketMiddleware
{

    public function handle($request, Closure $next, $bucketName)
    {
        $bucket = new TokenBucket($bucketName);

        if ($bucket->request()) {
            return $next($request);
        }else {
            throw new TooManyTryException();
        }
    }

}