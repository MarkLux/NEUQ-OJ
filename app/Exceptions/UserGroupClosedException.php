<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-9
 * Time: 下午1:35
 */

namespace NEUQOJ\Exceptions;


class UserGroupClosedException extends BaseException
{
    protected $code = 2004;

    protected $data = "User Group Is Closed.";
}