<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-9
 * Time: 下午2:53
 */

namespace NEUQOJ\Exceptions;


class UserNotInGroupException extends BaseException
{
    protected $code = 2006;
    protected $data = 'User Is Not In This Group';
}