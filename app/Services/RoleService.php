<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-10
 * Time: 上午1:21
 */

namespace NEUQOJ\Services;


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

    public function __construct(PrivilegeService $privilegeService,RoleRepository $RoleRepo,UserRoleRepository $userRoleRelation,RolePriRepository $rolePrivilegeRelation)
    {
        $this->RoleRepo = $RoleRepo;
        $this->UserRoleRepo = $userRoleRelation;
        $this->RolePrRepo = $rolePrivilegeRelation;
        $this->PriSer = $privilegeService;
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

//            $privilege = array(
//                'privilege'=>$item['pri'],
//                'description'=>$item['description']
//            );

            if(!($this->PriSer->insert))
            $priId = $this->PriSer->getPrivilegeDetailByName($item)->id;
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

    function roleExisted(string $role):bool
    {
        $bool = $this->getRoleDetailByName($role);
        if($bool == NUll)
            return false;
        else
            return true;

    }
    /*
     * 删除角色 roles表 user_role_relations表 role_privilege_relations表
     */
    function deleteRole($roleId){
        $arr = array(
            'role_id'=>$roleId
        );
        $s1 =  $this->RoleRepo->deleteWhere($arr);
        $s2 = $this->UserRoleRepo->deleteWhere($arr);
        $s3 = $this->RolePrRepo->deleteWhere($arr);
        if($s1&&$s2&&$s3)
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
}