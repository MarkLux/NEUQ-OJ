<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-25
 * Time: 下午10:42
 */

namespace NEUQOJ\Exceptions\UserGroup;


use NEUQOJ\Exceptions\BaseException;

class NoticeNotBelongToGroupException extends BaseException
{
    protected $code = 2007;

    protected $data = "This Notice Is Not Belong to this Group";
}