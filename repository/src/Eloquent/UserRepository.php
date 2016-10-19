<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-19
 * Time: 下午7:49
 */

namespace NEUQOJ\Repository\Eloquent;


class UserRepository extends AbstractRepository
{
    function model()
    {
        return "NUEQOJ\Repository\Models\User";
    }
}