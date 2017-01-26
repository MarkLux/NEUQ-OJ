<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/26
 * Time: 下午12:52
 */

namespace NEUQOJ\Exceptions;

use NEUQOJ\Exceptions\BaseException;

class VerifyCodeExpiresException extends BaseException
{
    protected $code = 1021;
    protected $data = "VerifyCode Expires";
}