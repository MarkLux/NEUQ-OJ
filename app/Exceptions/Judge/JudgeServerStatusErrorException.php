<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/8/25
 * Time: 下午6:41
 */

namespace NEUQOJ\Exceptions\Judge;


use NEUQOJ\Exceptions\BaseException;

class JudgeServerStatusErrorException extends BaseException
{
    protected $code = 70002;
    protected $data = 'Judge Server Status Error';
}