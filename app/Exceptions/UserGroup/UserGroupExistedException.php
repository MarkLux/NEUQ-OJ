<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-29
 * Time: 上午11:17
 */

namespace NEUQOJ\Exceptions\UserGroup;

use NEUQOJ\Exceptions\BaseException;

class UserGroupExistedException extends BaseException
{
    protected $code = 2001;
    protected $data = "A user group with same name has been created";
}