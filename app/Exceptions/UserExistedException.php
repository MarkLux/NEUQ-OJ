<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-19
 * Time: 下午8:07
 */

namespace NEUQOJ\Exceptions;


class UserExistedException extends BaseException
{
    protected $code = 20003;
}