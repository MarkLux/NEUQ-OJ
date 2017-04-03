<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-14
 * Time: 下午10:20
 */

namespace NEUQOJ\Exceptions\Tag;


use NEUQOJ\Exceptions\BaseException;

class TagsUnchangedException extends BaseException
{
    protected $code =3006;

    protected $data = "tag unchanged";
}