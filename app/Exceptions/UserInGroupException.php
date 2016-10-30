<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-30
 * Time: 下午8:41
 */

namespace NEUQOJ\Exceptions;


class UserInGroupException extends BaseException
{
    protected $code = 2003;
    protected $data = "User Is Already In This Group";
}