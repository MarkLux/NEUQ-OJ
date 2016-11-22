<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-10-26
 * Time: 下午8:07
 */

namespace NEUQOJ\Services;

use NEUQOJ\Repository\Eloquent\PrivilegeRepository;
use NEUQOJ\Repository\Eloquent\UserRepository;
class PrivilegeService
{

    const TEACHER ='teacher';
    const ADMIN = 'admin';
    const RoleTea = 1;
    const RoleAdmin = 2;
    private $priRepo;
    public function __construct(PrivilegeRepository $privilegeRepository)
    {
        $this->priRepo = $privilegeRepository;
    }

    public function getPrivilegeDetailByName(string $name)
    {
        return $this->priRepo->getBy('name',$name)->first();
    }
    /*
     * 确认做出请求的用户的角色
     * 获取用户id
     * 数据库查询role字段
     * 为空定位学生 ，1 为教师 2 为管理员
     *
     */

//    public function confirmRole($id,UserRepository $userRepository)
//    {
//
//        $user = $userRepository->getBy('mobile',$id)->first();
//
//        $role = $user->role;
//        //dd($role);dd($user);
//        if($user->role)
//        {
//            if($role == 1)
//                return self::TEACHER;
//            if ($role == 2)
//                return self::ADMIN;
//        }
//        else
//            return false;
//    }


}