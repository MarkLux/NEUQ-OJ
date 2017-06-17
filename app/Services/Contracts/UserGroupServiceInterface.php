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
    /**
     *  用户组基本内容
     */

    // 基本获取函数

    function getGroupById(int $id,array $columns = ['*']);

    function getGroupDetail(int $id);

    function getGroupBy(string $param,string $value,array $columns=['*']);

    function getGroupByMult(array $condition,array $columns = ['*']);

    function getGroups(int $page,int $size,array $columns = ['*']);

    function getGroupCount():int;

//    function getGroupIndex(int $userId,int $groupId);

//    function getUpdateGroup(int $groupId);

    // 辅助判断

//    function isGroupExistByName(int $ownerId,string $name):bool;

    function isGroupExistById(int $id):bool;

    // 管理

    function createUserGroup(array $data,array $userIds=[]):int;

    function deleteGroup(int $groupId):bool;

    function updateGroup(array $data,int $groupId):bool;

    function changeGroupOwner(int $groupId,int $newOwnerId):bool;

//    function closeGroup(int $groupId):bool;
//
//    function openGroup(int $groupId):bool;

    // 搜索

    function searchGroupsCount(string $keyword):int;

    function searchGroupsBy(string $keyword,int $page = 1,int $size = 20);

    /**
     * 成员部分
     */

    // 获取

    function getGroupMembers(int $groupId,int $page,int $size);

    function getGroupMembersCount(int $groupId):int;

    // 用户判断

    function isUserGroupStudent(int $userId,int $groupId):bool;

    function isUserGroupOwner(int $userId,int $groupId):bool;

//    function isUserInGroup(int $userId,int $groupId):bool;

    // 加入和退出

    function isUserGroupFull(int $groupId):bool;

    // 验证的逻辑写到控制器里去

//    function joinGroupByPassword(int $userId,int $groupId,string $password):bool;
//
//    function joinGroupWithoutPassword(User $user,UserGroup $group):bool;


    // 管理

    function addMember(int $groupId,$userId):bool;

    function addMembers(int $groupId,array $userIds):bool;

    function deleteMember(int $groupId,array $userIds);

    function updateMemberInfo(int $userId,int $groupId,array $data):bool;


    /**
     * 公告
     */

    // 获取

    function getGroupNoticesCount(int $groupId):int;

    function getGroupNotices(int $groupId,int $page,int $size);

    function getSingleNotice(int $noticeId);

    // 管理

    function addNotice(array $data):bool;

    function deleteNotice(int $noticeId):bool;

    function updateNotice(int $noticeId,array $data):bool;

    // 辅助判断

    function isNoticeBelongToGroup(int $noticeId,int $groupId):bool;

    function isNoticeExist(int $noticeId):bool;

    /**
     * 辅助函数
     */

    function getGroupsUserIn(int $userId);

}