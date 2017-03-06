<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/26
 * Time: 下午1:59
 */

namespace NEUQOJ\Exceptions;


class UserIsActivatedException extends BaseException
{
    protected $code = 1025;
    protected $data = "User has been activated";
}