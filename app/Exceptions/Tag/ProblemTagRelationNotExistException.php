<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/4/6
 * Time: 下午9:47
 */

namespace NEUQOJ\Exceptions\Tag;


use NEUQOJ\Exceptions\BaseException;

class ProblemTagRelationNotExistException extends BaseException
{
    protected $code = 3008;
    protected $data = "Problem Tag Relation Not Exist !";
}