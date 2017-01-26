<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/26
 * Time: 下午1:25
 */

namespace NEUQOJ\Exceptions;


class UserLockedException extends BaseException
{
    protected $code = 1033;
    protected $data = "Your Account has been locked.";
}