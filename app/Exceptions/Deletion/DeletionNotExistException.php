<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-28
 * Time: 下午2:49
 */

namespace NEUQOJ\Exceptions\Deletion;


use NEUQOJ\Exceptions\BaseException;

class DeletionNotExistException extends BaseException
{
    protected $code = 7001;

    protected $data = "Can't find the deletion";
}