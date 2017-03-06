<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 16/11/23
 * Time: 下午8:38
 */

namespace NEUQOJ\Exceptions;

class MobileExistException extends BaseException
{
    protected $code = 1002;
    protected $data = "The mobile number is exist";
}