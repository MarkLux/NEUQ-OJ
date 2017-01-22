<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/22
 * Time: 上午9:28
 */

namespace NEUQOJ\Exceptions\ProblemGroup;


use NEUQOJ\Exceptions\BaseException;

class HomeworkNotExistException extends BaseException
{
    protected $code = 8005;
    protected $data = "Homework Doesn't Exist";
}