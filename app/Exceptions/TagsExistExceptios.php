<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-14
 * Time: 下午9:10
 */

namespace NEUQOJ\Exceptions;


class TagsExistExceptios extends BaseException
{
    protected $code = 3005;

    protected $data = "Tags Existed or Tag's content unchanged";
}