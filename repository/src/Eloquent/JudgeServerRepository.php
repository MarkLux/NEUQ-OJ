<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/8/23
 * Time: 上午10:03
 */

namespace NEUQOJ\Repository\Eloquent;


use NEUQOJ\Repository\Traits\InsertWithIdTrait;

class JudgeServerRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\JudgeServer";
    }

    use InsertWithIdTrait;
}