<?php
/**
 * Created by PhpStorm.
 * User: mark
<<<<<<< HEAD
 * Date: 16-10-20
 * Time: 下午10:47
=======
 * Date: 16-10-19
 * Time: 下午8:06
>>>>>>> origin/mark
 */

namespace NEUQOJ\Exceptions;


class BaseException extends \Exception
{
    protected $data;

    public function getData()
    {
        return $this->data;
    }
}