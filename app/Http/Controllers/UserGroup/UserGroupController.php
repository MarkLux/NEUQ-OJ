<?php

namespace NEUQOJ\Http\Controllers\UserGroup;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\UserGroup\UserGroupClosedException;
use NEUQOJ\Exceptions\UserGroup\UserGroupIsFullException;
use NEUQOJ\Exceptions\UserGroup\UserGroupNotExistException;
use NEUQOJ\Exceptions\UserGroup\UserNotInGroupException;
use NEUQOJ\Facades\Permission;
use NEUQOJ\Services\UserGroupService;
use Illuminate\Support\Facades\Hash;
use NEUQOJ\Http\Controllers\Controller;

class UserGroupController extends Controller
{
    private $userGroupService;

    //
    public function __construct(UserGroupService $service)
    {
        $this->userGroupService = $service;
    }

    /**
     *  ------------------------------------
     *  -------------- 正常操作 -------------
     *  ------------------------------------
     */

    /**
     * 获取用户组列表
     */

    public function getGroups(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'size' => 'integer|min:1|max:50',
            'page' => 'integer|min:1',
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $total_count = $this->userGroupService->getGroupCount();

        $size = $request->input('size', 20);
        $page = $request->input('page', 1);

        $groups = $this->userGroupService->getGroups($page, $size, [
            'id', 'is_closed', 'privacy', 'name', 'created_at', 'max_size'
        ]);


        return response()->json([
            'code' => 0,
            'data' => [
                'groups' => $groups,
                'total_count' => $total_count
            ],
        ]);
    }

    /**
     * 用户组的详细信息
     */

    public function getGroup(Request $request, int $groupId)
    {
        if (!Permission::checkPermission($request->user->id, ['access-user-group'])) {
            if (!$this->userGroupService->isUserInGroup($request->user->id, $groupId)) {
                throw new NoPermissionException();
            }
        }

        return response()->json([
            'code' => 0,
            'data' => $this->userGroupService->getGroupDetail($groupId)
        ]);
    }

    /**
     * 分页的用户查询
     */

    public function getMembers(Request $request, int $groupId)
    {
        Utils::validateCheck($request->all(), [
            'page' => 'min:1|integer',
            'size' => 'min:1|integer'
        ]);

        $page = $request->input('page', 1);
        $size = $request->input('size', 20);

        // 权限设定：只有组内的人和管理员才能查看到成员的列表

        if (!Permission::checkPermission($request->user->id, ['access-user-group'])) {
            if (!$this->userGroupService->isUserInGroup($request->user->id, $groupId)) {
                throw new NoPermissionException();
            }
        }

        $count = $this->userGroupService->getGroupMembersCount($groupId);
        $members = $this->userGroupService->getGroupMembers($groupId, $page, $size);

        return response()->json([
            'code' => 0,
            'data' => [
                'count' => $count,
                'members' => $members
            ]
        ]);

    }

    /**
     *  获取自己所在的小组
     */

    public function getMyGroups(Request $request)
    {
        // 分页问题考虑了一下，先不做

        return response()->json([
            'code' => 0,
            'data' => $this->userGroupService->getGroupsUserIn($request->user->id)
        ]);
    }


    /**
     * 创建新用户组
     */

    public function createNewGroup(Request $request)
    {
        //表单检查
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100|string',
            'description' => 'string|max:512',
            'privacy' => 'required|integer|min:0|max:2',
            'max_size' => 'required|integer|max:500',
            'password' => 'min:6|max:20',//明文显示
            'is_closed' => 'boolean'
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        if (!Permission::checkPermission($request->user->id, ['create-user-group'])) {
            throw new NoPermissionException();
        }

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'privacy' => $request->privacy,
            'max_size' => $request->max_size,
            'is_closed' => $request->input('is_closed', 0)
        ];

        if (intval($request->privacy) == 1) {
            $data['password'] = Utils::pwGen($request->input('password', '123456')); // 有点智障的逻辑
        }

        $data['owner_id'] = $request->user->id;

        $groupId = $this->userGroupService->createUserGroup($data, $request->input('users', []));

        return response()->json([
            "code" => 0,
            "data" => [
                "group_id" => $groupId
            ]
        ]);

    }

    /**
     * 加入用户组
     */

    public function joinGroup(Request $request, int $groupId)
    {
        Utils::validateCheck($request->all(), [
            'password' => 'string'
        ]);

        $group = $this->userGroupService->getGroupById($groupId, ['privacy', 'password', 'is_closed']);

        if ($group == null)
            throw new UserGroupNotExistException();

        if ($group->is_closed) {
            throw new UserGroupClosedException();
        }

        if ($this->userGroupService->isUserGroupFull($groupId)) {
            // 考虑高并发的话这里可能有点问题
            throw new UserGroupIsFullException();
        }

        if ($group->privacy == 0) {
            // 公开的小组
            if (!($this->userGroupService->addMember($groupId, $request->user->id))) {
                throw new InnerError("fail to add user into group");
            }
        } else if ($group->privacy == 1) {
            // 加密
            $password = $request->input('password', null);
            if ($password == null || !Utils::pwCheck($password, $group->password)) {
                throw new PasswordErrorException();
            }

            if (!($this->userGroupService->addMember($groupId, $request->user->id))) {
                throw new InnerError("fail to add user into group");
            }
        } else if ($group->privacy == 2) {
            // 前端可以直接不显示privacy为2的用户组
            return new NoPermissionException();
        }

        return response()->json([
            'code' => 0
        ]);
    }


    /**
     *模糊搜索
     */

    public function searchGroups(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'size' => 'integer|min:1',
            'page' => 'integer|min:1',
            'keyword' => 'string|required|max:30'
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $size = $request->input('size', 20);
        $page = $request->input('page', 1);

        $total_count = $this->userGroupService->searchGroupsCount($request->keyword);

        if ($total_count > 0)
            $data = $this->userGroupService->searchGroupsBy($request->keyword, $page, $size);
        else
            $data = [];

        return response()->json([
            "code" => 0,
            "data" => [
                'groups' => $data,
                'count' => $total_count
            ]
        ]);
    }

    /**
     * 退出
     */

    public function quitGroup(Request $request, $groupId)
    {
        //验证逻辑：用户应该在退出用户组之前先输入他的密码来验证
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|max:255'
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        if (!Utils::pwCheck($request->password, $request->user->password))
            throw new PasswordErrorException();

        if (!$this->userGroupService->deleteMember($groupId, [$request->user->id]))
            throw new InnerError("fail to delete user from group");

        return response()->json([
            'code' => 0
        ]);
    }

    /**
     *  ------------------------------------
     *  -------------- 管理操作 -------------
     *  ------------------------------------
     */

    /**
     * 获取自己创建的用户组
     */

    public function getCreatedGroups(Request $request)
    {
        if (!Permission::checkPermission($request->user->id,['create-user-group'])) {
            throw new NoPermissionException();
        }

        return response()->json([
            'code' => 0,
            'data' => $this->userGroupService->getGroupBy('owner_id',$request->user->id,['id','name','created_at','privacy','is_closed','max_size'])
        ]);
    }

    /**
     * 修改用户组基本信息
     */

    public function updateGroupInfo(Request $request, int $groupId)
    {
        Utils::validateCheck($request->all(),[
            'is_closed' => 'boolean',
            'name' => 'string|max:100',
            'description' => 'string|max:512',
            'max_size' => 'integer|min:1|max:500',
            'password' => 'string|min:6',
            'privacy' => 'integer|min:0|max:2'
        ]);

        if (!Permission::checkPermission($request->user->id,['manage-user-group'])) {
            // 检查是否是创建者
            if (!$this->userGroupService->isUserGroupOwner($request->user->id,$groupId)) {
                throw new NoPermissionException();
            }
        }

        $data = [];

        if ($request->has('is_closed')) {
            $data['is_closed'] = $request->is_closed;
        }

        if ($request->has('name')) {
            $data['name'] = $request->name;
        }

        if ($request->has('description')) {
            $data['description'] = $request->description;
        }

        if ($request->has('privacy')) {
            $data['privacy'] = $request->privacy;
        }

        if ($data['privacy'] == 1 && $request->has('password')) {
            $data['password'] = $request->password;
        }

        if ($request->has('max_size')) {
            $data['max_size'] = $request->max_size;
        }

        if (!$this->userGroupService->updateGroup($data,$groupId)) {
            throw new InnerError("fail to update user group info");
        }

        return response()->json([
            'code' => 0
        ]);

    }

    /**
     * 增加用户
     */

    public function addMembers(Request $request, int $groupId)
    {
        Utils::validateCheck($request->all(),[
            'users' => 'required|array'
        ]);

        if (!Permission::checkPermission($request->user->id,['manage-user-group'])) {
            // 检查是否是创建者
            if (!$this->userGroupService->isUserGroupOwner($request->user->id,$groupId)) {
                throw new NoPermissionException();
            }
        }

        if ($this->userGroupService->isUserGroupFull($groupId)) {
            throw new UserGroupIsFullException();
        }

        if (!$this->userGroupService->addMembers($groupId,$request->users)) {
            throw new InnerError("fail to add members");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    /**
     * 删除用户
     */

    public function deleteMembers(Request $request, int $groupId)
    {
        Utils::validateCheck($request->all(),[
            'user_ids' => 'required|array'
        ]);

        if (!Permission::checkPermission($request->user->id,['manage-user-group'])) {
            // 检查是否是创建者
            if (!$this->userGroupService->isUserGroupOwner($request->user->id,$groupId)) {
                throw new NoPermissionException();
            }
        }

        if (!$this->userGroupService->deleteMember($groupId,$request->user_ids)) {
            throw new InnerError("fail to delete members");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    /**
     * 更新组内用户（单个）
     */

    public function updateMemberInfo(Request $request, int $groupId)
    {
        Utils::validateCheck($request->all(),[
            'user_id' => 'required|integer',
            'user_tag' => 'required|string|max:255'
        ]);


        if (!Permission::checkPermission($request->user->id,['manage-user-group'])) {
            // 检查是否是创建者
            if (!$this->userGroupService->isUserGroupOwner($request->user->id,$groupId)) {
                throw new NoPermissionException();
            }
        }

        if (!$this->userGroupService->updateMemberInfo($request->user_id,$groupId,['user_tag' => $request->user_tag])) {
            throw new InnerError("fail to update member info");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    /**
     * 更新自己在组内的名片
     */

    public function updateMyTag(Request $request,int $groupId)
    {
        Utils::validateCheck($request->all(),[
            'newTag' => 'required|max:255'
        ]);

        if (!$this->userGroupService->isUserGroupStudent($request->user->id,$groupId)) {
            throw new UserNotInGroupException();
        }

        if (!$this->userGroupService->updateMemberInfo($request->user->id,$groupId,['user_tag' => $request->newTag])) {
            throw new InnerError("fail to update your tag");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    /**
     * 转让用户组
     */

    public function changeOwner(Request $request, int $groupId)
    {

        Utils::validateCheck($request->all(), [
            'password' => 'required|min:6|max:255',
            'newOwnerId' => 'required|integer'
        ]);

        //检查密码
        if (!Utils::pwCheck($request->password, $request->user->password))
            throw new PasswordErrorException();

        $group = $this->userGroupService->getGroupById($groupId, ['id', 'owner_id']);

        if ($group == null) {
            throw new UserGroupNotExistException();
        }

        if (!Permission::checkPermission($request->user->id, ['manage-user-group'])) {
            if ($group->owner_id != $request->user->id) {
                throw new NoPermissionException();
            }
        }

        if (!Permission::checkPermission($request->newOwnerId, ['create-user-group'])) {
            throw new NoPermissionException();
        }

        if (!$this->userGroupService->changeGroupOwner($groupId, $request->newOwnerId)) {
            throw new InnerError("fail to chang group owner");
        }

        return response()->json([
            'code' => 0
        ]);

    }

    public function dismissGroup(Request $request, int $groupId)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|max:20'
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //检查密码
        if (!Utils::pwCheck($request->password, $request->user->password))
            throw new PasswordErrorException();

        if (!Permission::checkPermission($request->user->id, ['manage-user-group'])) {
            if (!$this->userGroupService->isUserGroupOwner($request->user->id,$groupId)) {
                throw new NoPermissionException();
            }
        }

        if (!$this->userGroupService->deleteGroup($groupId))
            throw new InnerError("Fail to delete Group ");

        return response()->json([
            'code' => 0
        ]);

    }

}
