<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-10
 * Time: 上午1:21
 */

namespace NEUQOJ\Services;


use NEUQOJ\Repository\Models\Role;
use NEUQOJ\Repository\Models\UserRoleRelation;
use NEUQOJ\Services\Contracts\RoleServiceInterface;
use NEUQOJ\user_role_relations;

class RoleService implements RoleServiceInterface
{
    private $RoleRepo;
    private $UserRoleRepo;
    public function __construct(Role $RoleRepo,UserRoleRelation $userRoleRelation)
    {
        $this->RoleRepo = $RoleRepo;
        $this->UserRoleRepo = $userRoleRelation;
    }

    function hasRole(int $userId,string $role):bool
    {
        $bool = $this->UserRoleRepo->get($userId,$role);
        return $bool;
    }

    function createRole(array $data)
    {

    }

    function giveRoleTo(int $userId,string $role)
    {
       return $this->UserRoleRepo->insert($userId,$role);

    }

    function getRole(string $name):Role
    {
       return $this->UserRoleRepo->get($name)->first();
    }

    function deleteRole($roleId){
     //
    }

    function updateRole(array $condition,array $data)
    {
        //
    }
}