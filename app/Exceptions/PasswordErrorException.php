<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-22
 * Time: 下午4:17
 */

namespace NEUQOJ\Exceptions;


class PasswordErrorException extends BaseException
{
    protected $code = 1006;

    protected $data = "Password Error";
}