<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-26
 * Time: 下午7:52
 */

namespace NEUQOJ\Services;

use NEUQOJ\Http\Requests\Request;
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
     *创建用户组，如果该用户已经创建过一个同名的用户组
     * 使用返回-1的方法出现了bug
     */
    public function createUserGroup($ownerId,array $data)
    {
        $userGroup = $this->userGroupRepo->getByMult([
            'owner_id' => $ownerId,
            'name' => $data['name']
        ])->first();

        if($userGroup!=null)
            return false;

        $data['owner_id'] = $ownerId;
        $data['size'] = 0;

        return $this->userGroupRepo->insert($data);
    }

    public function isGroupExist($groupId)
    {
        $group = $this->userGroupRepo->get($groupId);
        if($group!=null)
            return true;
        return false;
    }

    public function getGroupByName($ownerId,$groupName)
    {
        return $this->userGroupRepo->getByMult([
            'owner_id' => $ownerId,
            'name' => $groupName
        ])->first();
    }

    public function getGroupById($groupId)
    {
        return $this->userGroupRepo->get($groupId)->first();
    }

    //检测组是否已经满了，这里直接用字段判断的
    public function isGroupFull($groupId)
    {
        $group = $this->userGroupRepo->get($groupId);

        if($group!=null)
        {
            if($group->size >= $group->max_size)
                return true;
        }

        return false;
    }

    /**
     * 分页获取用户组所有成员对象,只读取基本信息
     * 请注意用户组的所有者并不算“成员”，它和用户组之间的关系并不写在relation表中
     */
    public function getMembers($groupId)
    {
        //TODO 考虑需要组织哪些信息(join表)
        //TODO 分页展示

        $users = $this->relationRepo->getBy('group_id',$groupId,['user_id']);

        return $users;
    }

    //判断一个用户是否已经在用户组里了
    public function isUserInGroup($userId,$groupId)
    {
        $relation = $this->relationRepo->getByMult([
            'user_id' => $userId,
            'group_id' => $groupId
        ])->first();

        if($relation!=null)
            return true;
        else
            return false;
    }

    //将用户加入某个组
    public function addUserTo($user,$groupId)
    {
        $data = [
            'group_id' => $groupId,
            'user_id' => $user->id,
            'user_name' =>$user->name
        ];

        return $this->relationRepo->insert($data);
    }

    //修改某个用户在组内的信息
    public function editUserInfo($userId,$groupId,$data)
    {
        $condition = [
          'user_id' => $userId,
          'group_id' => $groupId
        ];

        return $this->relationRepo->updateWhere($condition,$data);
    }

}