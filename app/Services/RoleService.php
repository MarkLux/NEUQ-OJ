<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-10
 * Time: 上午1:21
 */

namespace NEUQOJ\Services;


use Illuminate\Support\Facades\DB;
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
        $arr = $this->UserRoleRepo->getBy('user_id',$userId);
        $roleId = $this->RoleRepo->getBy('name',$role)->first()->id;

        foreach ($arr as $item) {
            if($item['role_id'] ==$roleId)
                return true;
        }

        return false;
    }

    /*
     * 创造角色
     * 对表Roles，role_privilege_relations 操作
     * 返回值限定为角色的id，方法失败的话返回-1
     */
    function createRole(array $data):int
    {

        $role = [
            'name'=>$data['role'],
            'description'=>$data['description'],
        ];


        $rid = -1;

        //检查输入合法性
        $privileges = $this->PriRepo->getIn('id',$data['privilege']);
        if(count($data['privilege']!=count($privileges)))
            return -1;


        //创建事件，对数据库操作的有哪项失败的话就自动回滚
        DB::transaction(
            function ()use($role,$data,$rid)
            {
                $rid = $this->RoleRepo->insertWithId($role);

                $relations = [];

                foreach ($data['privilege'] as $pid)
                {
                    array_push($relations,[
                        'role_id' => $rid,
                        'privilege_id' => $pid,
                        'role' => $role['name']
                    ]);
                }

                $this->RolePrRepo->insert($relations);
            }
        );

        return $rid;
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
        $role = array(
            'id'=>$roleId
        );

        $rolePr = array(
            'role_id'=>$roleId
        );

        $flag = -1;


        DB::transation(
            function ()use($role,$rolePr,$roleId)
            {
                if($this->isRoleBelongTo($roleId))
                {
                    $this->UserRoleRepo->deleteWhere($rolePr);
                }
                $this->RoleRepo->deleteWhere($role);

                $this->RolePrRepo->deleteWhere($rolePr);

            }
        );

        $flag = 1;
        return $flag;
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