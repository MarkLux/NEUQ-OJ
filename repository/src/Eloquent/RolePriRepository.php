<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-19
 * Time: 上午5:15
 */

namespace NEUQOJ\Repository\Eloquent;


class RolePrivilegeRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\RolePrivilegeRepository";
    }
    function deleteBy($roleId)
    {
        return $this->model->where('roleId',$roleId)->destory();
    }

}