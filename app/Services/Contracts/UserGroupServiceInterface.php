<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-8
 * Time: 下午10:18
 */

namespace NEUQOJ\Services\Contracts;

use NEUQOJ\Repository\Models\User;
use NEUQOJ\Repository\Models\UserGroup;


interface UserGroupServiceInterface
{
    /*
  *基本信息部分
  */

    function getGroupById(int $id,array $columns = ['*']);

    function getGroupBy(string $param,string $value,array $columns = ['*']);

    function getGroupByMult(array $condition,array $columns = ['*']);

    function getGroups(int $page,int $size,array $columns=['*']);

    function getGroupCount():int;

    //有可能改成private
    function isGroupExistByName(int $ownerId,string $name):bool;

    function isGroupExistById(int $id):bool;

    function createUserGroup(User $owner,array $data):int;

    //显示用户组的信息面板
    function getGroupIndex(int $groupId,User $user);

    /*
    *用户关系部分
    */

    function isUserGroupStudent(int $userId,int $groupId):bool;

    function isUserGroupOwner(int $userId,int $groupId):bool;

    //判断用户组是否已经满了
    function isUserGroupFull(int $groupId):bool;

    //验证失败抛出异常
    function joinGroupByPassword(User $user,UserGroup $group,string $password):bool;

    function joinGroupWithoutPassword(User $user,UserGroup $group):bool;

    function updateGroup(array $data,int $groupId):bool;

    //修改用户在小组中的身份注明
    function updateUserInfo(int $userId,int $groupId,array $data):bool;

    function deleteUserFromGroup(int $userId,int $groupId):bool;

    function deleteGroup(User $user,int $groupId);

    function changeGroupOwner(int $groupId,int $newOwnerId):bool;

    public function closeGroup(int $groupId):bool;

    public function openGroup(int $groupId):bool;

    //查找用户组部分，根据实际情况增删
    function searchGroupsCount(string $keyword):int;

    function searchGroupsBy(string $keyword,int $page = 1,int $size = 15);


    /*
    *公告板
    */

    function addNotice(int $groupId,array $data):bool;

    function getGroupNoticesCount(int $groupId):int;

    function getGroupNotices(int $groupId,int $page,int $size);

    function getSingleNotice(int $noticeId);

    function deleteNotice(int $noticeId):bool;

    function updateNotice(int $noticeId,array $data):bool;

    function isNoticeBelongToGroup(int $noticeId,int $groupId):bool;

    function isNoticeExist(int $noticeId):bool;

    /*
    *组成员部分
    */

    function getGroupMembers(int $groupId,int $page,int $size);

    function getGroupMembersCount(int $groupId):int;

    /*
     * 用户辅助函数
     */
    //获取某个用户加入的用户组列表
    function getGroupsUserIn(int $userId);
}