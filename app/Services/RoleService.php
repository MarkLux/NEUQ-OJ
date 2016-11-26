<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-10
 * Time: 上午1:21
 */

namespace NEUQOJ\Services;


use NEUQOJ\Repository\Eloquent\PrivilegeRepository;
use NEUQOJ\Repository\Eloquent\RolePriRepository;
use NEUQOJ\Repository\Eloquent\RoleRepository;
use NEUQOJ\Repository\Eloquent\UserRoleRepository;
use NEUQOJ\Services\Contracts\RoleServiceInterface;


class RoleService implements RoleServiceInterface
{
    private $RoleRepo;
    private $UserRoleRepo;
    private $RolePrRepo;
    private $PriSer;
    private $PriRepo;

    public function __construct(PrivilegeRepository $privilegeRepository,PrivilegeService $privilegeService,RoleRepository $RoleRepo,UserRoleRepository $userRoleRelation,RolePriRepository $rolePrivilegeRelation)
    {
        $this->RoleRepo = $RoleRepo;
        $this->UserRoleRepo = $userRoleRelation;
        $this->RolePrRepo = $rolePrivilegeRelation;
        $this->PriSer = $privilegeService;
        $this->PriRepo = $privilegeRepository;
    }

    function hasRole(int $userId,string $role):bool
    {
        $Role = $this->UserRoleRepo->get($userId,$role);
        if($Role == NULL)
            return false;
        else
            return true;
    }

    /*
     * 创造角色
     * 对表Roles，role_privilege_relations 操作
     */
    function createRole(array $data)
    {
        /*
         * 将角色名和描述 插入 Roles表
         */
        $role = [
            'name'=>$data['role'],
            'description'=>$data['description'],
        ];
        if(!($this->RoleRepo->insert($role)))
            return false;

        /*
         * 获取role_id ,privilege_id
         */
        $arr = $this->getRoleDetailByName($data['role']);
        $roleId = $arr->id;

        /*
             * 遍历权限数组 循环将角色 权限插入
              */
        foreach ($data['privilege'] as $item){

            $privilege = array(
                'name'=>$item['pri'],
                'description'=>$item['description']
            );

            if(!($this->PriRepo->insert($privilege)))
                return false;

            $priId = $this->PriSer->getPrivilegeDetailByName($item['pri'])->id;
            $rolePrRelation = array(
              'role_id' => $roleId,
                'privilege_id'=>$priId,
                'role'=>$data['role'],
            );
            if(!($this->RolePrRepo->insert($rolePrRelation)))
                return false;
        }

        return true;

    }
    /*
     * 找到role 对应role_id
     * 将user_id role_id 插入user_role_relations表
     * 再　填充　user_pri_re
     */
    function giveRoleTo(int $userId,string $role)
    {
        $roleData = $this->getRoleDetailByName($role);

        $roleId = $roleData->id;

        $data = array(
            'user_id'=>$userId,
            'role_id'=>$roleId,
        );
       if(!($this->UserRoleRepo->insert($data)))
        return false;


        if(!($this->PriSer->givePrivilegeTo($userId,$roleId)))
            return false;

        return true;
    }

    function roleExisted(string $role)
    {
         return $this->getRoleDetailByName($role);
    }
    /*
     * 删除角色 roles表 user_role_relations表 role_privilege_relations表
     */
    function deleteRole($roleId){
        $PrivilegeData = $this->PriSer->getRolePrivilege($roleId);

        $role = array(
            'id'=>$roleId
        );

        $rolePr = array(
            'role_id'=>$roleId
        );


        /*
         * 哪个用户有这个角色 就删关系
         */
        if($this->isRoleBelongTo($roleId))
            if(!($this->UserRoleRepo->deleteWhere($rolePr)))
                return false;


        if(!($this->RoleRepo->deleteWhere($role)))
             return false;


        if (!($this->RolePrRepo->deleteWhere($rolePr)))
            return false;



        foreach ($PrivilegeData as $item)
        {

            $pri = array(
                'id'=>$item->privilege_id
            );

            /*
             * 查找对应权限对应的角色数组
             */
            $arr = $this->RolePrRepo->getBy('privilege_id',$item->privilege_id);


            /*
             * 对应的角色数组 元素 个数等于0 表示当前权限是这个角色独有的 删除角色后 用户对应权限也被剥夺 否则用户仍具有该权限
             */

            if(count($arr)==0) {
                if (!($this->PriSer->deletePrivilege($pri)))
                    return false;
            }
            else
                continue;

        }
        return true;
    }

    function updateRole(array $condition,array $data)
    {
        $this->RoleRepo->updateWhere($condition,$data);
    }

    function updateRolePri(array $condition,array $data)
    {
        $this->RolePrRepo->updateWhere($condition,$data);
    }
    function getRoleDetailById($roleId)
    {
        return $this->RoleRepo->get($roleId)->first();
    }

    function getRoleDetailByName($name)
    {
        return $this->RoleRepo->getBy('name',$name)->first();
    }

    function isRoleBelongTo($roleId):bool
    {
        if($this->UserRoleRepo->getBy('role_id',$roleId)->first())
            return true;
        else return false;
    }
}