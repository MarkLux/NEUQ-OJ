<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-22
 * Time: 下午9:55
 */

namespace NEUQOJ\Exceptions;


class RoleExistedException extends BaseException
{
    protected $code = 2001;

    protected $data = "Role Existed";
}