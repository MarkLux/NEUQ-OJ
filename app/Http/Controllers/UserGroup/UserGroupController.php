<?php

namespace NEUQOJ\Http\Controllers\UserGroup;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\UserGroup\UserGroupNotExistException;
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

    public function getGroups(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'size' => 'integer|min:1|max:50',
            'page' => 'integer|min:1',
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $total_count = $this->userGroupService->getGroupCount();

        $size = $request->input('size',20);
        $page = $request->input('page',1);

        $groups = $this->userGroupService->getGroups($page,$size,['owner_name','owner_id','id','is_closed','password','name','created_at'])->toArray();

        //添加小组是否公开的标记
        foreach ($groups as &$group)
        {
            if($group['password'] == null)
                $group['is_public'] = 1;
            else
                $group['is_public'] = 0;

            unset($group['password']);
        }

        return response()->json([
            'code' => 0,
            'data' => $groups,
            'page_count' => ($total_count%$size)?intval($total_count/$size+1):($total_count/$size)
        ]);
    }

    /**
     * 创建新用户组
     * TODO:用户权限检查
     */
    public function createNewGroup(Request $request)
    {
        //表单检查
        $validator = Validator::make($request->all(),[
            'name' => 'required|max:100|string',
            'description' => 'max:512',
            'max_size' => 'required|integer|max:300',
            'password' => 'min:6|max:20',//明文显示
            'is_closed' => 'boolean'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'max_size' => $request->max_size,
            'password' => $request->password?bcrypt($request->password):null,
            'is_closed' => $request->input('is_closed',0)
        ];

        $groupId = $this->userGroupService->createUserGroup($request->user,$data);

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

    public function joinGroup(Request $request,$groupId)
    {
        $group = $this->userGroupService->getGroupById($groupId,['password']);

        if($group == null)
            throw new UserGroupNotExistException();


        if($group->password == null)
            $this->userGroupService->joinGroupWithoutPassword($request->user,$group);
        else
        {
            $validator = Validator::make($request->all(), [
                'password' => 'required|max:20'
            ]);

            if ($validator->fails())
                throw new FormValidatorException($validator->getMessageBag()->all());
            $this->userGroupService->joinGroupByPassword($request->user,$group,$request->password);

            return response()->json([
                "code" => 0
            ]);
        }
    }

    /**
     * 分页的用户查询
     */

    public function getMembers(Request $request,$groupId)
    {
        $groupId = intval($groupId);

        if(!$this->userGroupService->isGroupExistById($groupId))
            throw new UserGroupNotExistException();


        $total_count = $this->userGroupService->getGroupMembersCount($groupId);

        $validator = Validator::make($request->all(),[
            'size' => 'integer|min:1',
            'page' => 'integer|min:1'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $size = $request->input('size',20);
        $page = $request->input('page',1);


        if(!empty($total_count))
            $data = $this->userGroupService->getGroupMembers($groupId,$page,$size);
        else
            $data = null;

        return response()->json([
            "code" => 0,
            "data" => $data,
            "page_count" => ($total_count%$size)?intval($total_count/$size+1):($total_count/$size)
        ]);
    }
    /**
     *模糊搜索
     */
    public function searchGroups(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'size' => 'integer|min:1',
            'page' => 'integer|min:1',
            'keyword' => 'required|max:30'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $size = $request->input('size',20);
        $page = $request->input('page',1);

        $total_count = $this->userGroupService->searchGroupsCount($request->keyword);

        if($total_count > 0)
            $data = $this->userGroupService->searchGroupsBy($request->keyword,$page,$size);
        else
            $data = [];

        return response()->json([
            "code" => 0,
            "data" => $data,
            "page_count" => ($total_count%$size)?intval($total_count/$size+1):($total_count/$size)
        ]);
    }

    public function quitGroup(Request $request,$groupId)
    {
        //验证逻辑：用户应该在退出用户组之前先输入他的密码来验证
        $validator = Validator::make($request->all(),[
            'password' => 'required|min:6|max:255'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        if(!Hash::check($request->password,$request->user->password))
            throw new PasswordErrorException();

        if(!$this->userGroupService->deleteUserFromGroup($request->user->id,$groupId))
            throw new InnerError("fail to delete user from group");

        return response()->json([
            "code" => 0
        ]);
    }

    public function changeOwner(Request $request,$groupId)
    {
        $groupId = intval($groupId);

        $validator = Validator::make($request->all(),[
            'password' => 'required|min:6|max:255',
            'newOwnerId' => 'required|integer'
        ]);

        if(!$this->userGroupService->isGroupExistById($groupId))
            throw new UserGroupNotExistException();
        if(!$this->userGroupService->isUser)

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());
        if(!$this->userGroupService->isUserGroupOwner($request->user->id,$groupId))
            throw new NoPermissionException();

        //检查密码
        if(!Utils::pwCheck($request->password,$request->user->password))
            throw new PasswordErrorException();

        //检查当前登录用户是否是组的拥有者
        if(!$this->userGroupService->isUserGroupOwner($request->user->id,$groupId))
            throw new NoPermissionException();

        if(!$this->userGroupService->changeGroupOwner($groupId,$request->newOwnerId))
            throw new InnerError("Fail to change owner");

        return response()->json([
            "code" => 0
        ]);

    }

    public function closeGroup(Request $request,$groupId)
    {
        $groupId = intval($groupId);

        $validator = Validator::make($request->all(),[
            'password' => 'required|min:6|max:255'
        ]);

        if(!$this->userGroupService->isGroupExistById($groupId))
            throw new UserGroupNotExistException();

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());
        if(!$this->userGroupService->isUserGroupOwner($request->user->id,$groupId))
            throw new NoPermissionException();

        //检查密码
        if(!Hash::check($request->password,$request->user->password))
            throw new PasswordErrorException();

        if(!$this->userGroupService->closeGroup($groupId))
            throw new InnerError("Fail to close group");

        return response()->json([
            "code" => 0
        ]);
    }

    public function openGroup(Request $request,$groupId)
    {
        $groupId = intval($groupId);

        $validator = Validator::make($request->all(),[
            'password' => 'required|min:6|max:255'
        ]);

        if(!$this->userGroupService->isGroupExistById($groupId))
            throw new UserGroupNotExistException();

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());
        if(!$this->userGroupService->isUserGroupOwner($request->user->id,$groupId))
            throw new NoPermissionException();

        //检查密码
        if(!Hash::check($request->password,$request->user->password))
            throw new PasswordErrorException();

        if(!$this->userGroupService->openGroup($groupId))
            throw new InnerError("Fail to open group");

        return response()->json([
            "code" => 0
        ]);
    }

    public function dismissGroup(Request $request,int $groupId)
    {
        $validator = Validator::make($request->all(),[
            'password' => 'required|min:6|max:20'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        if(!$this->userGroupService->isGroupExistById($groupId))
            throw new UserGroupNotExistException();

        if(!$this->userGroupService->isUserGroupOwner($request->user->id,$groupId))
            throw new NoPermissionException();

        //检查密码
        if(!Hash::check($request->password,$request->user->password))
            throw new PasswordErrorException();

        if(!$this->userGroupService->deleteGroup($request->user,$groupId))
            throw new InnerError("Fail to delete Group :".$groupId);

        return response()->json([
            'code' => 0
        ]);

    }

    //获取当前登录用户所参加的小组列表
    public function getGroupsUserIn(Request $request)
    {
        $groups = $this->userGroupService->getGroupsUserIn($request->user->id);

        if(empty($groups)) $groups = null;//？

        return response()->json([
            'code' => 0,
            'data' => $groups
        ]);
    }
}
