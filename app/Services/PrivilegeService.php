<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-10-26
 * Time: 下午8:07
 */

namespace NEUQOJ\Services;

use NEUQOJ\Exceptions\PrivilegeNotExistException;
use NEUQOJ\Repository\Eloquent\PrivilegeRepository;
use NEUQOJ\Repository\Eloquent\RolePriRepository;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Repository\Eloquent\UsrPriRepository;

class PrivilegeService
{


    private $priRepo;
    private $rolePriRepo;
    private $userPriRepo;
    public function __construct(PrivilegeRepository $privilegeRepository,RolePriRepository $rolePriRepository,UsrPriRepository $usrPriRepository)
    {
        $this->priRepo = $privilegeRepository;
        $this->rolePriRepo = $rolePriRepository;
        $this->userPriRepo = $usrPriRepository;
    }

    public function getPrivilegeDetailByName(string $name)
    {
        return $this->priRepo->getBy('name',$name)->first();
    }

    /*
     * 获取角色对应的权利
     */
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
           if(!($this->userPriRepo->insert($content)))
               return false;

        }
        return true;
    }
    /*
     * 判断用户是否具有某项权利
     * 查user_pri_relation
     */
    public function hasNeededPrivilege(string $privilegeNeeded,$user_id)
    {
        $arr = $this->getPrivilegeDetailByName($privilegeNeeded);

        $pri_id = $arr->privilege_id;

        $data = array(
            'user_id'=>$user_id,
            'privilege_id'=>$pri_id,
        );
        if(!($this->userPriRepo->getByMult($data)))
            return false;
        else
            return true;
    }
}