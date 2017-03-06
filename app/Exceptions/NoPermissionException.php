<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-24
 * Time: 下午7:24
 */

namespace NEUQOJ\Exceptions;


class NoPermissionException extends BaseException
{
    protected $code = 1007;

    protected $data = "No Permission";
}