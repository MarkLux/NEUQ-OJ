<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-8
 * Time: 下午10:16
 */

namespace NEUQOJ\Services\Contracts;


interface UserServiceInterface
{
    /*
    *NOTICE:有关取出模型的方法不一定需要限定返回的类型，可能会找不到而返回null
    */
    function getUserById(int $userId);

    function getUserBy(string $param,$value);

    function getUserByMult(array $condition);
    //使用where方法

    function getUsers(array $data):array;

    function updateUserById(int $userId,$data):bool;

    function updateUser(array $condition,$data):array;

    //可能会有更新多个用户的方法

    function createUser(array $data):bool;

    function lockUser(int $userId):bool;

    function unlockUser(int $userId):bool;

    function isUserExist(array $data):bool;
    //内部使用一次Where查询

    function login(string $key,string $password);

    function logout(int $userId);

    function getUserRole(int $userId);
}