<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-24
 * Time: 下午7:26
 */

namespace NEUQOJ\Exceptions;


class PrivilegeNotExistException extends BaseException
{
    protected $code = 1008;
    protected $data = "Privilege Not Exist";
}