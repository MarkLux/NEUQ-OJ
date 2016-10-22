<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-22
 * Time: 下午5:57
 */

namespace NEUQOJ\Exceptions;


class TokenExpireException extends BaseException
{
    protected $code = 1006;
}