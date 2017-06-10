<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17-06-09
 * Time: 下午10:18
 */

namespace NEUQOJ\Services;

use NEUQOJ\Repository\Eloquent\GroupNoticeRepository;
use NEUQOJ\Repository\Eloquent\UserGroupRelationRepository;
use NEUQOJ\Repository\Eloquent\UserGroupRepository;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Repository\Models\User;
use NEUQOJ\Repository\Models\UserGroup;
use NEUQOJ\Services\Contracts\UserGroupServiceInterface;

class UserGroupService implements UserGroupServiceInterface
{
    private $userGroupRepo;
    private $userRepo;
    private $userGroupRealitonRepo;
    private $noticeRepo;

    public function __construct(UserGroupRepository $userGroupRepo,UserRepository $userRepo,
                                UserGroupRelationRepository $userGroupRealitonRepo,GroupNoticeRepository $noticeRepo)
    {
        $this->userGroupRepo = $userGroupRepo;
        $this->userRepo = $userRepo;
        $this->userGroupRealitonRepo = $userGroupRealitonRepo;
        $this->noticeRepo = $noticeRepo;
    }

    /**
     *  用户组基本内容
     */

    // 基本获取函数

    function getGroupById(int $id, array $columns = ['*'])
    {
        return $this->userGroupRepo->get($id,$columns)->first();
    }

    function getGroupBy(string $param, string $value, array $columns = ['*'])
    {
        return $this->userGroupRepo->getBy($param,$value,$columns);
    }

    function getGroupByMult(array $condition, array $columns = ['*'])
    {
        return $this->userGroupRepo->getByMult($condition,$columns);
    }

    function getGroups(int $page, int $size, array $columns = ['*'])
    {
        // 分页获取用户组列表
        return $this->userGroupRepo->paginate($page,$size,$columns);
    }

    function getGroupCount(): int
    {
        return $this->userGroupRepo->getTotalCount();
    }

//    function getGroupIndex(int $userId, int $groupId)
//    {
//        // 用户组首页用多个接口去处理，但控制器中应该添加一个获取详细信息的接口
//
//    }

//    function getUpdateGroup(int $groupId)
//    {
//        // 暂时取消
//    }


    // 辅助判断

    function isGroupExistById(int $id): bool
    {
        return !($this->getGroupById($id,['id']) == null);
    }

    function createUserGroup(int $ownerId, array $data,array $users=[]): int
    {

    }

    function deleteGroup(User $user, int $groupId)
    {
        // TODO: Implement deleteGroup() method.
    }

    function updateGroup(array $data, int $groupId): bool
    {
        // TODO: Implement updateGroup() method.
    }

    function changeGroupOwner(int $groupId, int $newOwnerId): bool
    {
        // TODO: Implement changeGroupOwner() method.
    }

    function closeGroup(int $groupId): bool
    {
        // TODO: Implement closeGroup() method.
    }

    function openGroup(int $groupId): bool
    {
        // TODO: Implement openGroup() method.
    }

    function searchGroupsCount(string $keyword): int
    {
        // TODO: Implement searchGroupsCount() method.
    }

    function searchGroupsBy(string $keyword, int $page = 1, int $size = 20)
    {
        // TODO: Implement searchGroupsBy() method.
    }

    function getGroupMembers(int $groupId, int $page, int $size)
    {
        // TODO: Implement getGroupMembers() method.
    }

    function getGroupMembersCount(int $groupId): int
    {
        // TODO: Implement getGroupMembersCount() method.
    }

    function isUserGroupStudent(int $userId, int $groupId): bool
    {
        // TODO: Implement isUserGroupStudent() method.
    }

    function isUserGroupOwner(int $userId, int $groupId): bool
    {
        // TODO: Implement isUserGroupOwner() method.
    }

    function isUserInGroup(int $userId, int $groupId): bool
    {
        // TODO: Implement isUserInGroup() method.
    }

    function isUserGroupFull(int $groupId): bool
    {
        // TODO: Implement isUserGroupFull() method.
    }

    function joinGroupByPassword(User $user, UserGroup $group, string $password): bool
    {
        // TODO: Implement joinGroupByPassword() method.
    }

    function joinGroupWithoutPassword(User $user, UserGroup $group): bool
    {
        // TODO: Implement joinGroupWithoutPassword() method.
    }

    function addMember(int $groupId, array $userIds): bool
    {
        // TODO: Implement addMember() method.
    }

    function deleteMember(int $groupId, array $userIds): bool
    {
        // TODO: Implement deleteMember() method.
    }

    function updateMemberInfo(array $userInfo, int $groupId): bool
    {
        // TODO: Implement updateMemberInfo() method.
    }

    function getGroupNoticesCount(int $groupId): int
    {
        // TODO: Implement getGroupNoticesCount() method.
    }

    function getGroupNotices(int $groupId, int $page, int $size)
    {
        // TODO: Implement getGroupNotices() method.
    }

    function getSingleNotice(int $noticeId)
    {
        // TODO: Implement getSingleNotice() method.
    }

    function addNotice(int $groupId, array $data): bool
    {
        // TODO: Implement addNotice() method.
    }

    function deleteNotice(int $noticeId): bool
    {
        // TODO: Implement deleteNotice() method.
    }

    function updateNotice(int $noticeId, array $data): bool
    {
        // TODO: Implement updateNotice() method.
    }

    function isNoticeBelongToGroup(int $noticeId, int $groupId): bool
    {
        // TODO: Implement isNoticeBelongToGroup() method.
    }

    function isNoticeExist(int $noticeId): bool
    {
        // TODO: Implement isNoticeExist() method.
    }

    function getGroupsUserIn(int $userId)
    {
        // TODO: Implement getGroupsUserIn() method.
    }

}