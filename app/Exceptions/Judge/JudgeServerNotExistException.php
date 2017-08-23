<?php

/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/8/23
 * Time: 下午1:10
 */

namespace NEUQOJ\Exceptions\Judge;

use NEUQOJ\Exceptions\BaseException;

class JudgeServerNotExistException extends BaseException
{
    protected $code = 70001;
    protected $data = "No Such Judge Server";
}