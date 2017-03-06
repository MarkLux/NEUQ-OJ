<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-23
 * Time: 上午12:40
 */

namespace NEUQOJ\Exceptions;


class RoleNotExistException extends BaseException
{
    protected $code = 3002;

    protected $data = "Role Not Exist";
}