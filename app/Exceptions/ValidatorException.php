<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-22
 * Time: 上午10:52
 */

namespace NEUQOJ\Exceptions;


class ValidatorException extends BaseException
{

    protected $code = 20004;

    public function __construct(array $data=[''])
    {
        parent::__construct();
        $this->data = $data;
    }
}