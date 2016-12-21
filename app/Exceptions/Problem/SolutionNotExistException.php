<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-21
 * Time: 下午8:56
 */

namespace NEUQOJ\Exceptions\Problem;


use NEUQOJ\Exceptions\BaseException;

class SolutionNotExistException extends BaseException
{
    protected $code = 6004;
    protected $data = "Solution not exist";
}