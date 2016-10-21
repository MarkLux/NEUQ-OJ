<?php
namespace NEUQOJ\Common;
/**
 * Created by PhpStorm.
 * User: trons
 * Date: 16/10/12
 * Time: 下午8:55
 */
class FlowToken
{
    /**
     * 令牌桶速率
     */
    const RATE = 0.1;
    /**
     * 令牌桶最大容量
     */
    const DEFAULT_TOKEN = 100;
    /**
     * redis 过期时间
     */
    const TIMEOUT = 60 * 60;

    public static function isAllow($costToken, $oldToken, $oldTime){
        if($costToken < 0)
            throw new \RuntimeException();

        $current = self::currentToken($oldToken, $oldTime);
        if($current >= $costToken){
            $current -= $costToken;
            $allow = true;
        }else{
            $allow = false;
            $current = 0;
        }
        return [
            $allow,
            $current
        ];
    }

    private static function currentToken($oldToken, $oldTime){
        $add = min(
            (Utils::createTimeStamp() - $oldTime) * self::RATE,
            self::DEFAULT_TOKEN - $oldToken
        );
        return $add + $oldToken;
    }
}