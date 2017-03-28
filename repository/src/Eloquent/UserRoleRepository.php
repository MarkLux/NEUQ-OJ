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
    function model()
    {

        return "NEUQOJ\Repository\Models\UserRoleRelation";
    }

    function deleteBy($roleId)
    {
        return $this->model->where('roleId', $roleId)->destory();
    }

    function getUserPrivileges(int $userId)
    {
        /**
         * 原计划生成子表之后再join，如下：
         *
         * select distinct privilege_name from
         * (
         * (select role_name from user_role_relations
         * where user_id = ? ) temp
         * join role_privilege_relations
         * on temp.role_name =
         * role_privilege_relations.role_name
         * );
         *
         */

        /**
         * mysql-workbench 执行只需要9毫秒
         * postman发送请求到dd出来居然花了4秒？
         */

        return $this->model
            ->where('user_id', $userId)
            ->join('role_privilege_relations', 'user_role_relations.role_name', '=', 'role_privilege_relations.role_name')
            ->select('role_privilege_relations.privilege_name')
            ->distinct()
            ->get();
    }
}