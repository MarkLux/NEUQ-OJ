<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-27
 * Time: 下午9:53
 */

namespace NEUQOJ\Repository\Eloquent;


class DeletionLogRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\DeletionLog";
    }
}