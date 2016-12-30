<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-8
 * Time: 下午10:17
 */

namespace NEUQOJ\Services\Contracts;


interface TokenServiceInterface
{
    function hasToken(int $userId):bool;

    // private function createToken(int $userId,string $ip):string;
    //
    // private function updateToken(int $userId,string $ip):string;

    function makeToken(int $userId,string $ip):string;
    //内部是调用create和update

    function isTokenExpire(string $tokenStr):bool;

    function destoryToken(int $userId);

    function getUserIdByToken(string $tokenStr):int;
}