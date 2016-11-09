<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-30
 * Time: 下午8:13
 */

namespace NEUQOJ\Exceptions;


class UserGroupNotExistException extends BaseException
{
    protected $code = 2002;
    protected $data = "User Group Not Exist";
}