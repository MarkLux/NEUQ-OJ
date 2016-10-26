<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-24
 * Time: 下午8:44
 */

namespace NEUQOJ\Repository\Eloquent;


class TokenRepository extends AbstractRepository
{
//    protected $timestamps = false;

    function model()
    {
        return "NEUQOJ\Repository\Models\Token";
    }
}