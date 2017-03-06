<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/26
 * Time: 下午12:57
 */

namespace NEUQOJ\Exceptions;


class UserNotActivatedException extends BaseException
{
    protected $code = 1030;
    protected $data = "User is not activated now";
}