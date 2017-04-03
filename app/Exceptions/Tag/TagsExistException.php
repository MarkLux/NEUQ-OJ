<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-14
 * Time: 下午9:10
 */

namespace NEUQOJ\Exceptions\Tag;


use NEUQOJ\Exceptions\BaseException;

class TagsExistException extends BaseException
{
    protected $code = 3005;

    protected $data = "Tags Existed";
}