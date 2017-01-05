<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-21
 * Time: 下午3:46
 */

namespace NEUQOJ\Repository\Eloquent;


class RuntimeInfoRepository extends AbstractRepository
{
    public function model()
    {
       return "NEUQOJ\Repository\Models\RuntimeInfo";
    }
}