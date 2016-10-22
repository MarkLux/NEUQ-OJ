<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-22
 * Time: 下午12:56
 */

namespace NEUQOJ\Repository\Eloquent;


class TokenRepository extends AbstractRepository
{
    protected $timestamps =false;

    function model(){
        return "NEUQOJ\Repository\Models\Token";
    }
}