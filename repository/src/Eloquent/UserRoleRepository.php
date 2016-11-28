<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-19
 * Time: 上午5:12
 */

namespace NEUQOJ\Repository\Eloquent;


class UserRoleRepository extends AbstractRepository
{
    function model(){

        return "NEUQOJ\Repository\Models\UserRoleRelation";
    }

    function deleteBy($roleId)
    {
        return $this->model->where('roleId',$roleId)->destory();
    }
}