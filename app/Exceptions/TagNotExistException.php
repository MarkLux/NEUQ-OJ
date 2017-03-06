<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 17-1-14
 * Time: 下午2:12
 */

namespace NEUQOJ\Exceptions;


class TagNotExistException extends BaseException
{
    protected $code = 3007;
    protected $data = "Tag Not Exist";
}