<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/1/20
 * Time: 下午2:49
 */
namespace NEUQOJ\Services\Contracts;

use NEUQOJ\Repository\Models\User;

interface HomeworkServiceInterface
{
    //基本获取函数
    function getHomework(int $id,array $columns=['*']);

    function getHomeworkBy(string $param,string $value,array $columns=['*']);

    function getHomeworkIndex(int $userId = -1,int $HomeworkId);

    //一个小组内的所有作业列表（简略,没有设置分页）
    function getHomeworksInGroup(int $groupId);

    //添加
    function addHomework(User $user,int $userGroupId,array $data,array $problems):int;

    //修改
    function updateHomeworkInfo(User $user,int $homeworkId,array $data=[],array $problems=[]):bool;

    function deleteHomework(User $user,int $homeworkId):bool;

    //辅助判断函数

    function isHomeworkExist(int $homeworkId):bool;

    function isUserHomeworkOwner(int $userId,int $homeworkId):bool;

    function canUserAccessHomework(int $userId,int $homeworkId):bool;

    //状态,根据用户的权限设定组装不同的内容
    function getHomeworkStatus(int $userId,int $homewrokId);

    function getHomeworkRank(int $homeworkId);
}