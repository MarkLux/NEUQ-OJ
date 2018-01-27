<?php
namespace NEUQOJ\Common;

use Illuminate\Support\Facades\Redis;
use NEUQOJ\Services\CacheService;

class TokenBucket
{
    private $bucketKey;
    private static $MAX_CAPACITY = 20;
    private static $SPEED = 5;

    private static $REDIS_EXPIRE_TIME = 360;


    public function __construct($bucketKey)
    {
        $this->bucketKey = $bucketKey;
    }

    public function getLastToken()
    {
        $str =  Redis::get($this->bucketKey.'-bucket');
        return json_decode($str);
    }

    public function updateToken($timeStamp,$tokenNum)
    {
        Redis::setex($this->bucketKey.'-bucket',self::$REDIS_EXPIRE_TIME,json_encode([
            'timestamp' => $timeStamp,
            'last_token' => $tokenNum
        ]));
    }

    public function request()
    {
        $now = time();
        $lastToken = $this->getLastToken();
        if (Redis::exists($this->bucketKey.'-bucket') != 1) {
            // 还没有桶的话，创立一个
            $this->updateToken($now,self::$MAX_CAPACITY - 1);
            return true;
        }
        $currentToken = min(self::$MAX_CAPACITY,($now-$lastToken->timestamp)*self::$SPEED);
        if ($currentToken <= 0) {
            return false;
        }else {
            $this->updateToken($now,$currentToken - 1);
            return true;
        }
    }
}