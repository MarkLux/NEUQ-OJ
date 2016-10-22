<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-22
 * Time: 下午4:19
 */

namespace NEUQOJ\Exceptions;


class NeedLoginException extends BaseException
{
    protected $code = 1004;
}