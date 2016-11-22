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

    function getRes($userId,array $privileges)
    {
        //一个高耦合的写法
        return $this->model->where('user_id',$userId)->whereIn('privilege_id',$privileges)->get();
    }

}