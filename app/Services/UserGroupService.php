<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-26
 * Time: 下午7:52
 */

namespace NEUQOJ\Services;

use NEUQOJ\Repository\Eloquent\UserGroupRepository;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Repository\Eloquent\UserGroupRelationRepository;


class UserGroupService
{
    private $userRepo;
    private $userGroupRepo;
    private $relationRepo;

    public function __construct(UserRepository $userRepository,UserGroupRelationRepository $relationRepository,UserGroupRepository $userGroupRepository)
    {
        $this->userRepo = $userRepository;
        $this->userGroupRepo = $userGroupRepository;
        $this->relationRepo = $relationRepository;
    }

    /*
     *创建用户组，如果该用户已经创建过一个同名的用户组就返回-1
     */
    public function createUserGroup($userId,array $data)
    {
        $userGroup = $this->userGroupRepo->getByMult([
            'owner_id' => $userId,
            'name' => $data['name']
        ]);

        if($userGroup!=null)
            return -1;

        $data['owner_id'] = $userId;
        $data['size'] = 0;

        return $this->userGroupRepo->insert($data);
    }

    public function findGroupBy($ownerId,$name)
    {
        return $this->userGroupRepo->getByMult([
            'owner_id' => $ownerId,
            'name' => $name
        ]);
    }

    public function addUser($userId,$groupId)
    {
        $group = $this->userGroupRepo->get($groupId);

        if($group == null)
            return false;

        $this->relationRepo->insert([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);
    }

    //TODO 解决用户组大小的维护

}