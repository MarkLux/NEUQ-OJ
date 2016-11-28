<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-25
 * Time: 下午10:45
 */

namespace NEUQOJ\Exceptions\UserGroup;


use NEUQOJ\Exceptions\BaseException;

class NoticeNotExistException extends BaseException
{
    protected $code = 2008;

    protected $data = "Notice do not exist";
}