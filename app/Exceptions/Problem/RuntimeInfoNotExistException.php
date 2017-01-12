<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-21
 * Time: 下午8:52
 */

namespace NEUQOJ\Exceptions\Problem;


use NEUQOJ\Exceptions\BaseException;

class RuntimeInfoNotExistException extends BaseException
{
    protected $code = 6003;
    protected $data = "RuntimeInfo not exist";
}