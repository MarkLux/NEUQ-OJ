<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/6
 * Time: 下午3:35
 */

namespace NEUQOJ\Services\Contracts;

interface CacheServiceInterface
{
    function isCacheExist(string $key):bool;

    function setRankCache(string $key,array $ranks,int $timeout);

    function getRankCache(string $key);
}