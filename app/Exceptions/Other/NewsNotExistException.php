<?php

/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/3/26
 * Time: 下午8:59
 */

namespace NEUQOJ\Exceptions\Other;

use NEUQOJ\Exceptions\BaseException;

class NewsNotExistException extends BaseException
{
    protected $code = 9001;
    protected $data = "News Not Exist";
}