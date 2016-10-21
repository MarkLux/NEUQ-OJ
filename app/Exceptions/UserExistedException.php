<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-20
 * Time: 下午10:48
 */

namespace NEUQOJ\Exceptions;


class UserExistedException extends BaseException
{
    protected $code = 20003;
}