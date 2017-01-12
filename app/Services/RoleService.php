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

    public function hasRole(int $userId,string $role):bool
    {

        $arr = $this->UserRoleRepo->getBy('user_id',$userId,['role_id']);
        $roleId = $this->RoleRepo->getBy('name',$role,['id'])->first();


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
   public function createRole(array $data):int
    {

        $role = [
            'name'=>$data['role'],
            'description'=>$data['description'],
        ];


        $rid = -1;

        //检查输入合法性
        $privileges = $this->PriRepo->getIn('id',$data['privilege']);
        if(count($data['privilege']!=count($privileges)))
            return $rid;


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
    public function giveRoleTo(int $userId,string $role)
    {
        $roleData = $this->getRoleDetailByName($role,['id']);

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

    public function roleExisted(string $role)
    {
         return $this->getRoleDetailByName($role);
    }
    /*
     * 删除角色 roles表 user_role_relations表 role_privilege_relations表
     */
    public function deleteRole($roleId){
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

    public function updateRole(array $condition,array $data)
    {
        if($this->RoleRepo->updateWhere($condition,$data))
            return true;
        else
            return false;
    }

    public function updateRolePri(array $condition,array $data)
    {
        if($this->RolePrRepo->updateWhere($condition,$data))
            return true;
        else
            return false;
    }
    public function getRoleDetailById(int $roleId,array $columns=['*'])
    {
        return $this->RoleRepo->get($roleId,$columns)->first();
    }

    public function getRoleDetailByName(string $name,array $columns=['*'])
    {
        return $this->RoleRepo->getBy('name',$name,$columns)->first();
    }

    public function isRoleBelongTo(int $roleId):bool
    {
        if($this->UserRoleRepo->getBy('role_id',$roleId,['user_id'])->first())
            return true;
        else return false;
    }
}