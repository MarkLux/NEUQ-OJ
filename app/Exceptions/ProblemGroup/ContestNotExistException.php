<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17-1-7
 * Time: 下午4:58
 */

namespace NEUQOJ\Exceptions\ProblemGroup;


use NEUQOJ\Exceptions\BaseException;

class ContestNotExistException extends  BaseException
{
    protected $code = 8001;
    protected $data = "Contest Not Exist";
}