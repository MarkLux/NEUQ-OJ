<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-10-26
 * Time: 下午8:07
 */

namespace NEUQOJ\Services;

use NEUQOJ\Repository\Eloquent\PrivilegeRepository;
use NEUQOJ\Repository\Eloquent\RolePriRepository;
use NEUQOJ\Repository\Eloquent\UserRepository;
class PrivilegeService
{


    private $priRepo;
    private $rolePriRepo;
    public function __construct(PrivilegeRepository $privilegeRepository,RolePriRepository $rolePriRepository)
    {
        $this->priRepo = $privilegeRepository;
        $this->rolePriRepo = $rolePriRepository;
    }

    public function getPrivilegeDetailByName(string $name)
    {
        return $this->priRepo->getBy('name',$name)->first();
    }

    public function getRolePrivilege($roleId)
    {
        return $this->rolePriRepo->getBy('role_id',$roleId);
    }

    /*
     * 赋予对应用户　对应权限
     */
    public function givePrivilegeTo($userId,$roleId)
    {
        $arr = $this->getRolePrivilege($roleId);
        foreach ($arr as $item)
        {
            $content = array(
                'user_id'=>$userId,
                'privilege_id'=>$item['privilege_id']
            );
           if(!($this->UserPriRepo->insert($content)))
               return false;

        }
        return true;
    }

}