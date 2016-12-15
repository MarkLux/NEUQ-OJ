<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-14
 * Time: 下午10:20
 */

namespace NEUQOJ\Exceptions;


class TagsUnchangedExceptions extends BaseException
{
    protected $code =3005;

    protected $data = "tag unchanged";
}