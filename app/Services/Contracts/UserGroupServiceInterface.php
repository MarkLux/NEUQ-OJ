<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-11-8
 * Time: 下午10:18
 */

namespace NEUQOJ\Services\Contracts;

use NEUQOJ\Repository\Models\User;


interface UserGroupServiceInterface
{
    /*
  *基本信息部分
  */

    function getGroupById(int $id);

    function getGroupBy(string $param,string $value);

    function getGroupByMult(array $condition);

    //有可能改成private
    function isGroupExistByName(int $ownerId,string $name):bool;

    function isGroupExistById(int $id):bool;

    function createUserGroup(User $owner,array $data):bool;

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
    function joinGroupByPassword(User $user,int $groupId,string $password):bool;

    function joinGroupByInvite(User $user,int $groupId):bool;

    function updateGroup(array $data,int $groupId):bool;

    //修改用户在小组中的身份注明
    function updateUserInfo(int $userId,int $groupId,array $data):bool;

    function deleteUser(int $userId,int $groupId):bool;

    function quitGroup(int $userId,int $groupId):bool;

    function deleteGroup(int $groupId):bool;

    function changeGroupOwner(int $groupId,int $newOwnerId);

    //查找用户组部分，根据实际情况增删
    function searchGroupsCount(array $condition):int;

    function searchGroupsBy(array $condition,string $orderBy,int $start,int $size):array;

    //还需要模糊查询

    function searchGroupsByNameLikeCount(string $likeName):int;

    function searchGroupsByNameLike(string $likeName,string $orderBy,int $start,int $size):array;

    /*
    *公告板
    */

    function addNotice(int $groupId,array $data);

    function getGroupNoticesCount(int $groupId):int;

    function getGroupNotices(int $groupId,int $start,int $size):array;

    /*
    *作业
    */

    function getGroupHomeworksCount(int $groupId):int;

    function getGroupHomeworks(int $groupId,int $start,int $size):array;

    /*
    *考试
    */

    function getGroupExamsCount(int $groupId):int;

    function getGroupExams(int $groupId,int $start,int $size):array;

    /*
    *组成员部分
    */

    function getGroupMembers(int $groupId,int $start,int $size):array;

    function getGroupMembersCount(int $groupId):int;
}