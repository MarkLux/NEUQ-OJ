<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-24
 * Time: 下午7:19
 */

namespace NEUQOJ\Repository\Eloquent;


class UsrPriRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\UserPrivilegeRelation";
    }
}