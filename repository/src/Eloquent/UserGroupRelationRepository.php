<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-26
 * Time: 下午7:58
 */

namespace NEUQOJ\Repository\Eloquent;


class UserGroupRelationRepository extends AbstractRepository
{
    function model()
    {
        return "NUEQOJ\Repository\Models\UserGroupRelation";
    }
}