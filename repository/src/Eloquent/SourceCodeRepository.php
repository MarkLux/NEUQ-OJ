<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-16
 * Time: 下午6:06
 */

namespace NEUQOJ\Repository\Eloquent;


use NEUQOJ\Repository\Traits\InsertWithIdTrait;

class SourceCodeRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\SourceCode";
    }

    use InsertWithIdTrait;
}