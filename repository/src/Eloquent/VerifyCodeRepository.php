<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/26
 * Time: 上午11:37
 */

namespace NEUQOJ\Repository\Eloquent;


class VerifyCodeRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\VerifyCode";
    }
}