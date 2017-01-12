<?php

/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-13
 * Time: 下午5:40
 */

namespace NEUQOJ\Exceptions\Problem;

use NEUQOJ\Exceptions\BaseException;

class ProblemNotExistException extends BaseException
{
    protected $code = 6001;
    protected $data = "Problem Not Exist";
}