<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-21
 * Time: 下午3:44
 */

namespace NEUQOJ\Repository\Eloquent;


class CompileInfoRepository extends AbstractRepository
{
    public function model()
    {
        return "NEUQOJ\Repository\Models\CompileInfo";
    }
}