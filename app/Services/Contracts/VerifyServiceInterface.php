<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/26
 * Time: 上午10:47
 */
namespace NEUQOJ\Services\Contracts;

use NEUQOJ\Repository\Models\User;

interface VerifyServiceInterface
{
    function sendVerifyEmail(User $user):bool;

    function activeUserByEmailCode(int $userId,string $verifyCode):bool;

    function sendCheckEmail(User $user):bool;

    function checkUserByEmailCode(int $userId,string $verifyCode):bool;
}