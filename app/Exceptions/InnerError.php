<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-30
 * Time: 下午8:43
 */

namespace NEUQOJ\Exceptions;


class InnerError extends BaseException
{
    protected $code = 0000;
    protected $data = "System Inner Error";
}