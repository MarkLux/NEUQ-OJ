<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/8
 * Time: 下午4:31
 */

namespace NEUQOJ\Exceptions\ProblemGroup;


use NEUQOJ\Exceptions\BaseException;

class ContestEndedException extends BaseException
{
    protected $code = 8004;
    protected $data = "Contest ended.";
}