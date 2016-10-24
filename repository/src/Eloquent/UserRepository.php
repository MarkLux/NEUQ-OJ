<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-24
 * Time: 下午8:42
 */

namespace NEUQOJ\Repository\Eloquent;


class UserRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\User";
    }
}