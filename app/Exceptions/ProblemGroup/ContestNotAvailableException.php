<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/8
 * Time: 下午4:20
 */

namespace NEUQOJ\Exceptions\ProblemGroup;


use NEUQOJ\Exceptions\BaseException;

class ContestNotAvailableException extends BaseException
{
    protected $code = 8003;
    protected $data = "Contest is not available now.";
}