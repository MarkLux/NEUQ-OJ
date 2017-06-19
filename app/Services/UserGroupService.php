<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17-06-09
 * Time: 下午10:18
 */

namespace NEUQOJ\Services;

use Illuminate\Support\Facades\DB;
use NEUQOJ\Exceptions\UserGroup\UserInGroupException;
use NEUQOJ\Repository\Eloquent\GroupNoticeRepository;
use NEUQOJ\Repository\Eloquent\ProblemGroupRepository;
use NEUQOJ\Repository\Eloquent\UserGroupRelationRepository;
use NEUQOJ\Repository\Eloquent\UserGroupRepository;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Services\Contracts\UserGroupServiceInterface;

class UserGroupService implements UserGroupServiceInterface
{
    private $userGroupRepo;
    private $userRepo;
    private $userGroupRelationRepo;
    private $noticeRepo;
    private $problemGroupRepo;

    public function __construct(UserGroupRepository $userGroupRepo,UserRepository $userRepo,
                                UserGroupRelationRepository $userGroupRelationRepo,
                                GroupNoticeRepository $noticeRepo,ProblemGroupRepository $problemGroupRepo)
    {
        $this->userGroupRepo = $userGroupRepo;
        $this->userRepo = $userRepo;
        $this->userGroupRelationRepo = $userGroupRelationRepo;
        $this->noticeRepo = $noticeRepo;
        $this->problemGroupRepo = $problemGroupRepo;
    }

    /**
     *  用户组基本内容
     */

    // 基本获取函数

    function getGroupById(int $id, array $columns = ['*'])
    {
        return $this->userGroupRepo->get($id,$columns)->first();
    }

    // 这个真的蛋疼，为了join得到创建者的name等信息还要专门写一个函数。。。

    function getGroupDetail(int $id)
    {
        return $this->userGroupRepo->getDetailInfo($id)->first();
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
        return $this->userGroupRepo->paginate($page,$size,[],$columns);
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

    // 创建用户组，users是初始化的组内用户关系映射

    function createUserGroup(array $data,array $users=[]): int
    {
        $gid = -1;

        if (!empty($users)) {
            DB::transaction(function () use ($data,$users,&$gid) {
                $gid = $this->userGroupRepo->insertWithId($data);
                foreach ($users as &$user) {
                    $user['group_id'] = $gid;
                    // 其余的属性要提前设置好
                }
                $this->userGroupRelationRepo->insert($users);
            });
            return $gid;
        } else {
            $gid = $this->userGroupRepo->insertWithId($data);
            return $gid;
        }

    }

    // 删除用户组将删除大量资源，后期优化时应该思考一下性能影响

    function deleteGroup(int $groupId):bool
    {
        $flag = false;

        DB::transaction(function ()use(&$flag,$groupId){
            // 基本组
            $this->userGroupRepo->deleteWhere(['id' => $groupId]);
            // 用户关系
            $this->userGroupRelationRepo->deleteWhere(['group_id' => $groupId]);
            // 作业
            $this->problemGroupRepo->deleteWhere(['user_group_id' => $groupId]);
            // 公告
            $this->noticeRepo->deleteWhere(['group_id' => $groupId]);
            // 考虑是否清理solutions

            $flag = true;
        });

        return $flag;
    }

    // 更新用户组基本设置

    function updateGroup(array $data, int $groupId): bool
    {
        // 控制器里注意限制组装data时能更新的字段
        return $this->userGroupRepo->update($data,$groupId) == 1;
    }

    function changeGroupOwner(int $groupId, int $newOwnerId): bool
    {
        return $this->userGroupRepo->update(['owner_id' => $newOwnerId],$groupId) == 1;
    }

    // 搜索

    function searchGroupsCount(string $keyword): int
    {
       $pattern = '%'.$keyword.'%';

       return $this->userGroupRepo->getWhereLikeCount($pattern);
    }

    function searchGroupsBy(string $keyword, int $page = 1, int $size = 20)
    {
        $pattern = '%'.$keyword.'%';

        return $this->userGroupRepo->getWhereLike($pattern,$page,$size);
    }

    /**
     * 成员部分
     */

    function getGroupMembers(int $groupId, int $page = 1, int $size = 20)
    {
        return $this->userGroupRelationRepo->getGroupMembers($groupId,$page,$size);
    }

    function getGroupMembersCount(int $groupId): int
    {
        return $this->userGroupRelationRepo->getMemberCountById($groupId);
    }

    function isUserGroupStudent(int $userId, int $groupId): bool
    {
        return !($this->userGroupRelationRepo
                ->getByMult(['user_id' => $userId,'group_id' => $groupId])
                ->first() == null);
    }

    function isUserGroupOwner(int $userId, int $groupId): bool
    {
        return $this->userGroupRepo->get($groupId,['owner_id'])->first()->owner_id == $userId;
    }

    function isUserInGroup(int $userId, int $groupId): bool
    {
        return  $this->isUserGroupStudent($userId,$groupId) || $this->isUserGroupOwner($userId,$groupId);
    }

    function isUserGroupFull(int $groupId): bool
    {
        $maxSize = $this->userGroupRepo->get($groupId,['max_size'])->first()->max_size;

        return $this->getGroupMembersCount($groupId) > $maxSize;
    }

    function addMember(int $groupId, $userId): bool
    {
        // 不做判断的话可能引发插入异常

        if ($this->isUserInGroup($userId,$groupId)) {
            throw new UserInGroupException();
        }

        return $this->userGroupRelationRepo->insert(['user_id' => $userId,'group_id' => $groupId]) == 1;
    }

    function addMembers(int $groupId, array $users): bool
    {
        // 先判断有没有重复的
        // todo 可以考虑忽略已经在用户组里的，性能消耗自己做测试
        // todo 类似的逻辑应该使用sql的exists语法来检查是否存在不合理的数据
        $userIds = [];
        foreach ($users as &$user) {
            $userIds[] = $user['user_id'];
            $user['group_id'] = $groupId;
        }

        if (!$this->userGroupRelationRepo->checkUsersIn($groupId,$userIds)) {
            throw new UserInGroupException();
        }

        return $this->userGroupRelationRepo->insert($users) == count($users);
    }

    function deleteMember(int $groupId, array $userIds)
    {
        return $this->userGroupRelationRepo->deleteMembers($groupId,$userIds);
    }

    function updateMemberInfo(int $userId, int $groupId,array $data): bool
    {
        return $this->userGroupRelationRepo->updateWhere(['group_id' => $groupId,'user_id' => $userId],$data) == 1;
    }

    /**
     * 用户组新闻界面
     */

    function getGroupNoticesCount(int $groupId): int
    {
        return $this->noticeRepo->getWhereCount(['group_id' => $groupId]);
    }

    function getGroupNotices(int $groupId, int $page, int $size)
    {
        return $this->noticeRepo->paginate($page,$size,['group_id' => $groupId],['id','title','created_at']);
    }

    function getSingleNotice(int $noticeId)
    {
       return $this->noticeRepo->get($noticeId)->first();
    }

    function addNotice(array $data): bool
    {
        return $this->noticeRepo->insert($data) == 1;
    }

    function deleteNotice(int $noticeId): bool
    {
        return $this->noticeRepo->deleteWhere(['id' => $noticeId]) == 1;
    }

    function updateNotice(int $noticeId, array $data): bool
    {
        return $this->noticeRepo->updateWhere(['id' => $noticeId],$data) == 1;
    }

    function isNoticeBelongToGroup(int $noticeId, int $groupId): bool
    {
        $notice = $this->noticeRepo->get($noticeId,['group_id'])->first();

        if ($notice != null) {
            if ($notice->group_id == $groupId)
                return true;
        }

        return false;
    }

    function isNoticeExist(int $noticeId): bool
    {
        return !($this->noticeRepo->get($noticeId,['id'])->first() == null);
    }

    function getGroupsUserIn(int $userId)
    {
        return $this->userGroupRelationRepo->getGroupsUserIn($userId);
    }

}