<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-26
 * Time: 下午7:52
 */

namespace NEUQOJ\Services;

use Illuminate\Support\Facades\Hash;
use NEUQOJ\Exceptions\UserGroup\NoticeNotExistException;
use NEUQOJ\Exceptions\UserGroup\UserGroupIsFullException;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\UserGroup\UserGroupClosedException;
use NEUQOJ\Exceptions\UserGroup\UserGroupExistedException;
use NEUQOJ\Exceptions\UserGroup\UserGroupNotExistException;
use NEUQOJ\Exceptions\UserGroup\UserInGroupException;
use NEUQOJ\Exceptions\UserGroup\UserNotInGroupException;
use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Repository\Eloquent\GroupNoticeRepository;
use NEUQOJ\Repository\Eloquent\UserGroupRepository;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Repository\Eloquent\UserGroupRelationRepository;
use NEUQOJ\Repository\Models\UserGroup;
use NEUQOJ\Services\Contracts\UserGroupServiceInterface;
use NEUQOJ\Repository\Models\User;
use NEUQOJ\Exceptions\InnerError;
use Illuminate\Support\Facades\DB;
use NEUQOJ\Services\DeletionService;


class UserGroupService implements UserGroupServiceInterface
{
    private $userRepo;
    private $userGroupRepo;
    private $relationRepo;
    private $noticeRepo;
    private $deletionService;

    public function __construct(UserRepository $userRepository,UserGroupRelationRepository $relationRepository,
                                UserGroupRepository $userGroupRepository,GroupNoticeRepository $noticeRepository,
                                DeletionService $deletionService
    )
    {
        $this->userRepo = $userRepository;
        $this->userGroupRepo = $userGroupRepository;
        $this->relationRepo = $relationRepository;
        $this->noticeRepo = $noticeRepository;
        $this->deletionService = $deletionService;
    }

    /**
     * 基本获取部分
     */

    public function getGroupById(int $groupId,array $columns = ['*'])
    {
        return $this->userGroupRepo->get($groupId,$columns)->first();
    }

    public function getGroupBy(string $param,string $value,array $columns = ['*'])
    {
        return $this->userGroupRepo->getBy($param,$value,$columns)->first();
    }

    public function getGroupByMult(array $condition,array $columns = ['*'])
    {
       return $this->userGroupRepo->getByMult($condition,$columns)->first();
    }

    public function getGroups(int $page,int $size,array $columns = ['*'])
    {
        return $this->userGroupRepo->paginate($page,$size,[],$columns);
    }

    public function getGroupCount():int
    {
        return $this->userGroupRepo->getTotalCount();
    }

    /**
     * 搜索
     */

    public function searchGroupsCount(string $keyword):int
    {
        $pattern = '%'.$keyword.'%';//在这里定义模式串
        //未支持嵌套
        return $this->userGroupRepo->getWhereLikeCount($pattern);
    }

    public function searchGroupsBy(string $keyword,int $page =1, int $size =15)
    {
        $pattern = '%'.$keyword.'%';

        return $this->userGroupRepo->getWhereLike($pattern,$page,$size);
    }

    /**
     * 辅助检测函数
     */

    public function isGroupExistById(int $groupId):bool
    {
        $group = $this->userGroupRepo->get($groupId,['id']);
        if($group!=null)
            return true;
        return false;
    }

    public function isGroupExistByName(int $ownerId, string $name):bool
    {
        $group = $this->userGroupRepo->getByMult([
            'owner_id' => $ownerId,
            'name' => $name
        ],['id'])->first();

        if($group!=null)
            return true;
        return false;
    }

    public function isUserGroupFull(int $groupId):bool
    {
        $group = $this->getGroupById($groupId);

        if($group == null)
            throw new UserGroupNotExistException();

        $size = $this->relationRepo->getMemberCountById($groupId);

        if($size >= $group->max_size)
            return true;
        else
            return false;
    }

    public function isUserGroupStudent(int $userId, int $groupId):bool
    {
        $relation = $this->relationRepo->getByMult([
            'user_id' => $userId,
            'group_id' => $groupId
        ])->first();

        if($relation == null)
            return false;
        return true;
    }

    public function isUserInGroup(int $userId,int $groupId):bool
    {
        return ($this->isUserGroupStudent($userId,$groupId)||$this->isUserGroupOwner($userId,$groupId));
    }

    public function isUserGroupOwner(int $userId, int $groupId):bool
    {
        $group = $this->userGroupRepo->get($groupId)->first();

        if($group == null)
            throw new UserGroupNotExistException();

        if($group->owner_id == $userId)
            return true;
        return false;
    }

    /*
     *基本操作部分
     */

    //创建
    public function createUserGroup(User $owner,array $data):int
    {
        if($this->isGroupExistByName($owner->id,$data['name']))
            throw new UserGroupExistedException();

        $data['owner_id'] = $owner->id;
        $data['owner_name'] = $owner->name;

        return $this->userGroupRepo->insertWithId($data);
    }

    //删除
    public function deleteGroup(User $user,int $groupId):bool
    {
        // 要删除很多关系 相关模型基本都设立了软删除功能
        // 目前涉及到 组员 考试 作业 公告等一系列内容

        //多张表数据操作，应该使用数据库事务

        DB::transaction(function ()use($groupId,$user){
            $this->userGroupRepo->deleteWhere(['id' =>$groupId]);

            $data = [
                'table_name' => 'UserGroup',
                'key' => $groupId,
                'user_id' => $user->id,
                'user_name' => $user->name
            ];

            $this->deletionService->createDeletion($data);

            $data = [];

            $relations = $this->relationRepo->getBy('group_id',$groupId,['id']);

            if($relations->count()!=0) //修正bug
            {
                foreach ($relations as $relation)
                {
                    array_push($data,[
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'table_name' => 'UserGroupRelation',
                        'key' => $relation->id
                    ]);
                }

                $this->deletionService->createDeletions($data);
                //删除用户关系
                $this->relationRepo->deleteWhere(['group_id' => $groupId]);
            }
            //删除公告
            //公告不记录在日志里
            $this->noticeRepo->deleteWhere(['group_id' => $groupId]);
        });

        return true;

        //TODO 删除作业等一系列相关数据
        //删除考试
        //...
    }

    //易主
    public function changeGroupOwner(int $groupId, int $newOwnerId):bool
    {
        if(!$this->userRepo->get($newOwnerId)->first() == null)
            throw new UserNotExistException();

        $data = ['owner_id' => $newOwnerId];
        return $this->userGroupRepo->update($data,$groupId) == 1;
    }

    //关闭
    public function closeGroup(int $groupId):bool
    {
        $data = ['is_closed' => 1];
        return $this->userGroupRepo->update($data,$groupId) == 1;
    }

    //开放
    public function openGroup(int $groupId):bool
    {
        $data = ['is_closed' => 0];
        return $this->userGroupRepo->update($data,$groupId) == 1;
    }

    //加入
    public function joinGroupByPassword(User $user, UserGroup $group, string $password):bool
    {
        //检测用户组的开放状态
        if($group->is_closed)
            throw new UserGroupClosedException();

        //检测用户组是否已经满了,不用辅助方法因为会多执行一次不必要的查询
        if($this->getGroupMembersCount($group->id) >= $group->max_size)
            throw new UserGroupIsFullException();

        //检测密码
        if(!Hash::check($password,$group->password))
            throw new PasswordErrorException();

        //检测用户是否已经在组内了

        if($this->isUserGroupStudent($user->id,$group->id)||$this->isUserGroupOwner($user->id,$group->id))
            throw new UserInGroupException();

        //更新数据库

        return $this->relationRepo->insert([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_code' => 'undefined',
            'user_tag' => 'undefined'
        ])==1;
    }

    public function joinGroupWithoutPassword(User $user,UserGroup $group):bool
    {
        //检测用户组的开放状态
        if($group->is_closed)
            throw new UserGroupClosedException();

        //检测用户组是否已经满了
        if($this->isUserGroupFull($group->id))
            throw new UserGroupIsFullException();

        //检测用户是否已经在组内了

        if($this->isUserGroupStudent($user->id,$group->id)||$this->isUserGroupOwner($user->id,$group->id))
            throw new UserInGroupException();

        //更新数据库

        return $this->relationRepo->insert([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_code' => 'undefined',
            'user_tag' => 'undefined'
        ])==1;

    }

    //更新组信息
    public function updateGroup(array $data, int $groupId):bool
    {
        return $this->userGroupRepo->update($data,$groupId) == 1;
    }

    /**
     * 成员部分
     */
    public function updateUserInfo(int $userId, int $groupId, array $data):bool
    {
        if(!$this->isGroupExistById($groupId))
            throw new UserGroupNotExistException();

        if(!$this->isUserGroupStudent($userId,$groupId))
            throw new UserNotInGroupException();

        $condition = [
            'user_id' => $userId,
            'group_id' => $groupId
        ];

        return $this->relationRepo->updateWhere($condition,$data);

    }

    public function getGroupMembersCount(int $groupId):int
    {
        return $this->relationRepo->getMemberCountById($groupId);
    }

    public function getGroupMembers(int $groupId, int $page, int $size)
    {
        //TODO 可能需要join一些信息

        return $this->relationRepo->paginate($page,$size,['group_id' => $groupId]);
    }

    /*
     *从某个组中删除用户（删除关系）
     *  */

    public function deleteUserFromGroup(int $userId, int $groupId):bool
    {
        //检查：用户组的所有者是不能退出用户组的（关系表中也没这个关系）

        if($this->isUserGroupOwner($userId,$groupId))
            throw new InnerError("Owner can't quit the group");

        $param = [
            'user_id' => $userId,
            'group_id' => $groupId
        ];

        //检查用户
        $relation = $this->relationRepo->getByMult($param)->first();

        if($relation == null)
            throw new UserNotInGroupException();

        return $this->relationRepo->deleteWhere($param) == 1;
    }

    /**
     * 小组信息
     */

    public function getGroupIndex(int $groupId, User $user)
    {
        // TODO: 需要参考页面原型去组织信息
    }

    /**
     * 公告板
     */

    public function getGroupNoticesCount(int $groupId):int
    {
        return $this->noticeRepo->getBy('group_id',$groupId)->count();
    }

    public function getGroupNotices(int $groupId, int $page, int $size)
    {
        return $this->noticeRepo->paginate($page,$size,['group_id' => $groupId],['title','created_at']);
    }

    public function addNotice(int $groupId,array $data):bool
    {
        $data['group_id'] = $groupId;

        return $this->noticeRepo->insert($data) == 1;
    }

    public function deleteNotice(int $noticeId): bool
    {
        return $this->noticeRepo->deleteWhere(['id' => $noticeId]) == 1;
    }

    public function updateNotice(int $noticeId, array $data): bool
    {
        return $this->noticeRepo->update($data,$noticeId) == 1;
    }

    public function getSingleNotice(int $noticeId)
    {
       return  $this->noticeRepo->get($noticeId)->first();

    }

    public function isNoticeBelongToGroup(int $noticeId, int $groupId): bool
    {
        $notice = $this->getSingleNotice($noticeId);
        if($notice == null)
            throw new NoticeNotExistException();
        if($notice->group_id != $groupId)
            return false;
        return true;
    }

    public function isNoticeExist(int $noticeId): bool
    {
        return $this->getSingleNotice($noticeId)!=null;
    }

    public function getGroupsUserIn(int $userId)
    {
        $relations = $this->relationRepo->getBy('user_id',$userId,['group_id']);

        $groupIds = [];

        foreach ($relations as $relation)
        {
            $groupIds[] = $relation->group_id;
        }

        $groups = $this->userGroupRepo->getIn('id',$groupIds,['owner_name','owner_id','name','created_at']);

        return $groups;
    }

}