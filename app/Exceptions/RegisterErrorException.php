<?php
/**
 * Created by PhpStorm.
 * User: hotown
 * Date: 16-11-10
 * Time: 下午11:04
 */

namespace NEUQOJ\Exceptions;

class RegisterErrorException extends BaseException
{
    protected $code = 1008;

    protected $data = "Register Fails";
}