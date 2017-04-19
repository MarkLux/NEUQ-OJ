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

    function resendVerifyEmail(User $user):bool;

    function activeUserByEmailCode(string $verifyCode):int;

    function sendResetPasswordEmail(string $email):bool;

    function checkUserByVerifyCode(string $verifyCode):int;
}