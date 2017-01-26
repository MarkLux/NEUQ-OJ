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
    function getUserById(int $userId,array $columns = ['*']);

    function getUserBy(string $param,$value,array $columns = ['*']);

    function getUserByMult(array $condition,array $columns = ['*']);
    //使用where方法

    function getUsers(array $data,array $columns = ['*']);

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

    function resetPasswordByOldPass(int $userId,string $oldPass,string $newPass):bool;

    function sendForgotPasswordEmail(int $userId):bool;

    function resetPasswordByVerifyCode(int $userId,string $verifyCode,string $newPass):bool;

    function loginUser(int $userId,string $ip);

    function getUserRole(int $userId);


}