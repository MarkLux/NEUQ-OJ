<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/26
 * Time: 下午12:54
 */

namespace NEUQOJ\Exceptions;


class VerifyCodeErrorException extends BaseException
{
    protected $code = 1022;
    protected $data = "Verify Code is Wrong";
}