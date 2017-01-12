<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/1/7
 * Time: 下午10:28
 */

namespace NEUQOJ\Exceptions;


class TopicNotExistException extends BaseException
{
    protected $code = 1002;

    protected $data = "Topic Not Existed";
}