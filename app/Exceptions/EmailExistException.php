<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 16/11/23
 * Time: 下午8:41
 */

namespace NEUQOJ\Exceptions;

class EmailExistException extends BaseException
{
    protected $code = 1001;
    protected $data = "The e-mail is exist";
}