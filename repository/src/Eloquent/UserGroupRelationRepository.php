<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-26
 * Time: 下午7:58
 */

namespace NEUQOJ\Repository\Eloquent;


use NEUQOJ\Repository\Contracts\SoftDeletionInterface;
use NEUQOJ\Repository\Traits\SoftDeletionTrait;

class UserGroupRelationRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\UserGroupRelation";
    }

    function getMemberCountById(int $groupId): int
    {
        return $this->model->where('group_id', $groupId)->count();
    }

    function getGroupMembers(int $groupId,int $page,int $size)
    {
        // 获取全部，不分页

        if ($page == -1) {
            return $this->model
                ->where('group_id',$groupId)
                ->leftJoin('users','user_group_relations.user_id','=','users.id')
                ->select('user_group_relations.*','users.name')
                ->get();
        }
        else {
            return $this->model
                ->where('group_id',$groupId)
                ->leftJoin('users','user_group_relations.user_id','=','users.id')
                ->select('user_group_relations.*','users.name')
                ->skip(--$page)
                ->take($size)
                ->get();
        }
    }

    // 检查多个用户是否在用户组中，插入前判断

    function checkUsersIn(int $groupId,array $userIds)
    {
        return $this->model->where('group_id',$groupId)
            ->whereIn('user_id',$userIds)
            ->count()  == 0;
    }

    function deleteMembers(int $groupId,array $userIds)
    {
        return $this->model
            ->where('group_id',$groupId)
            ->whereIn('user_id',$userIds)
            ->delete();
    }

    function getGroupsUserIn(int $userId)
    {
        return $this->model
            ->where('user_id',$userId)
            ->leftJoin('user_groups','user_group_relations.group_id','=','user_groups.id')
            ->select('user_groups.id','user_groups.name','user_group_relations.created_at','user_group_relations.user_tag')
            ->get();
    }
}