<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-22
 * Time: 下午4:01
 */

namespace NEUQOJ\Exceptions;


class UserNotExistException extends BaseException
{
    protected $code = 1002;

    protected $data = "User Not Existed";
}