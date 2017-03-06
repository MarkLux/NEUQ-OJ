<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/26
 * Time: 下午2:11
 */

namespace NEUQOJ\Exceptions\UserGroup;


use NEUQOJ\Exceptions\BaseException;

class OperationTooQuickException extends BaseException
{
    protected $code = 1023;
    protected $data = 'Your Operation is too quick,please wait.';
}