<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 16/11/23
 * Time: 下午8:47
 */

namespace NEUQOJ\Exceptions;

class NameExistException extends BaseException
{
    protected $code = 1001;
    protected $data = "The name is exist";
}