<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17-1-7
 * Time: 下午10:56
 */

namespace NEUQOJ\Exceptions\ProblemGroup;


use NEUQOJ\Exceptions\BaseException;

class LanguageErrorException extends BaseException
{
    protected $code = 8002;
    protected $data = "Cant use this language";
}