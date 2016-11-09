<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-9
 * Time: 下午2:09
 */

namespace NEUQOJ\Exceptions;


class UserGroupIsFullException extends BaseException
{
    protected $code = 2005;
    protected $data = 'User Group Is Full';
}