<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-21
 * Time: 下午8:50
 */

namespace NEUQOJ\Exceptions\Problem;


use NEUQOJ\Exceptions\BaseException;

class CompileInfoNotExistException extends BaseException
{
    protected $code = 6002;
    protected $data = "CompileInfo not exist";
}