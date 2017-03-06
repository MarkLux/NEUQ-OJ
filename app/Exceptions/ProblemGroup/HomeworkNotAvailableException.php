<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 17-1-25
 * Time: 上午11:41
 */

namespace NEUQOJ\Exceptions\ProblemGroup;


use NEUQOJ\Exceptions\BaseException;

class HomeworkNotAvailableException extends BaseException
{
    protected $code = 8006;
    protected $data = "Homework is not available now.";
}