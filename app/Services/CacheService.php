<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17-1-2
 * Time: 下午5:19
 */

namespace NEUQOJ\Services;

use Illuminate\Support\Facades\Redis;
use NEUQOJ\Common\RedisHelper;
use NEUQOJ\Services\Contracts\CacheServiceInterface;

class CacheService implements CacheServiceInterface
{
    public static $STATISTIC_KEY = 'solution-statistics';

    public function isCacheExist(string $key): bool
    {
        return Redis::exists($key) == 1;
    }

    public function setRankCache(string $key, array $ranks, int $timeout)
    {
        Redis::pipeline(function ($pipe) use ($ranks, $timeout, $key) {
            foreach ($ranks as $rank) {
                $value = json_encode($rank);
                $pipe->lpush($key, $value);
            }
            $pipe->expire($key, $timeout);//设定过期时间为60秒，每分钟刷新一次榜单。
        });
    }

    public function getRankCache(string $key)
    {
        $length = Redis::llen($key);
        $results = Redis::lrange($key, 0, $length - 1);
        $ranks = [];

        foreach ($results as $result) {
            $ranks[] = json_decode($result);
        }

        return $ranks;
    }

    public function setJudgeResult(string $key, array $res, $time)
    {
        Redis::setex($key, $time, json_encode($res));
    }

    public function getJudgeResult(string $solutionId)
    {
        return Redis::get('solution:' . $solutionId);
    }

    public function setStatisticCache(array $data, int $timeout)
    {
        Redis::setex(self::$STATISTIC_KEY,$timeout,json_encode($data));
    }

    public function getStatisticCache()
    {
        return json_decode(Redis::get(self::$STATISTIC_KEY));
    }
}