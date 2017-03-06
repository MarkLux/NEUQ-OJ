<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-23
 * Time: 上午8:47
 */

namespace NEUQOJ\Exceptions;


class ProblemKeyNotExistedException extends  BaseException
{
    protected $code = 3008;
    protected $data = "ProblemKeyNotExisted";
}