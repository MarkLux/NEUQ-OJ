<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\UserGroup\UserGroupNotExistException;
use NEUQOJ\Services\UserGroupService;

class UserGroupController extends Controller
{
    private $userGroupService;
    //
    public function __construct(UserGroupService $service)
    {
        $this->userGroupService = $service;
    }

    /**
     * 创建新用户组
     */
    public function createNewGroup(Request $request)
    {
        //表单检查
        $validator = Validator::make($request->all(),[
            'name' => 'required|max:100',
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
            'is_closed' => $request->is_closed
        ];

        $groupId = $this->userGroupService->createUserGroup($request->user,$data);

        //TODO 创建时应该尝试对指定的多个用户发送邀请



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
        $group = $this->userGroupService->getGroupById($groupId);

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
        if(!$this->userGroupService->isGroupExistById($groupId))
            throw new UserGroupNotExistException();

        $total_count = $this->userGroupService->getGroupMembersCount($groupId);

        $validator = Validator::make($request->all(),[
            'size' => 'integer|min:1|max:50',
            'page' => 'integer|min:1|max:500'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $size = $request->input('size',10);
        $page = $request->input('page',1);

        $data = $this->userGroupService->getGroupMembers($groupId,$page,$size);

        return response()->json([
            "code" => 0,
            "data" => $data,
            "page_count" => ($total_count%$size)?intval($total_count/$size+1):($total_count/$size)
        ]);
    }

    public function searchGroups(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'size' => 'integer|min:1|max:50',
            'page' => 'integer|min:1|max:500',
            'keyword' => 'required|max:30'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $size = $request->input('size',10);
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

}
