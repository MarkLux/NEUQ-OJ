<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-8
 * Time: 下午10:16
 */

namespace NEUQOJ\Services\Contracts;


use NEUQOJ\Repository\Models\User;
use NEUQOJ\Services\TokenService;

interface UserServiceInterface
{
    /*
    *NOTICE:有关取出模型的方法不一定需要限定返回的类型，可能会找不到而返回null
    */
    function getUserById(int $userId);

    function getUserBy(string $param,$value);

    function getUserByMult(array $condition);
    //使用where方法

    function getUsers(array $data);

    function updateUserById(int $userId,array $data):bool;

    function updateUser(array $condition,array $data):bool;

    //可能会有更新多个用户的方法

    function createUser(array $data):bool;

    function lockUser(int $userId):bool;

    function unlockUser(int $userId):bool;

    function isUserExist(array $data):bool;
    //内部使用一次Where查询

    function register(array $data):int;

    function login(array $data);

    function loginUser(int $userId,string $ip);

    function getUserRole(int $userId);


}