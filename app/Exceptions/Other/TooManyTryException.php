<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 2018/1/27
 * Time: 下午8:30
 */

namespace NEUQOJ\Exceptions\Other;


use NEUQOJ\Exceptions\BaseException;

class TooManyTryException extends BaseException
{
    protected $code = 9002;
    protected $data = '当前操作人数太多，请稍后重试';
}