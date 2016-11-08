<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\UserGroupExistedException;
use NEUQOJ\Exceptions\UserGroupNotExistException;
use NEUQOJ\Http\Requests;
use NEUQOJ\Services\UserGroupService;
use Illuminate\Support\Facades\Hash;

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
            'password' => 'min:6|max:20'//明文显示
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'max_size' => $request->max_size,
            'password' => $request->password?bcrypt($request->password):null,
        ];

        if(!$this->userGroupService->createUserGroup($request->user->id,$data))
            throw new InnerError();

//        if($request->members!=null)
//        {
//            //TODO 发送邀请给指定的用户，需要用到apply模块,未完成
//        }

        $group = $this->userGroupService->getGroupBy($request->user->id,$request->name);

        return response()->json([
            "code" => 0,
            "data" => [
                "group_id" => $group->id
            ]
        ]);

    }

    public function getGroupList()
    {
        //TODO 获取组列表并显示
    }

    public function getIndex(Request $request,$groupId)
    {
        $group = $this->userGroupService->getGroupById($groupId);

        if($group == null)
            throw new UserGroupNotExistException();

        //先判断访问者与用户组的关系

        if(!$this->userGroupService->isUserInGroup($request->user->id,$groupId)&&$group->owner_id!=$request->user->id)
            throw new NoPermissionException();

        //TODO 显示更多信息
        $data = $this->userGroupService->getMembers($groupId);

        return response()->json($data);
    }

    public function joinGroup(Request $request,$groupId)
    {
        $group = $this->userGroupService->getGroupById($groupId);

        if($group == null)
            throw new UserGroupNotExistException();

        if(!Hash::check($request->password,$group->password))
            throw new PasswordErrorException();

        if($this->userGroupService->isUserInGroup($request->user->id,$groupId))
            throw new UserInGroupExcepstion();

        if($this->userGroupService->addUserTo($request->user,$groupId))
            return response()->json([
                "code" => 0
            ]);
        else
            throw new InnerError();
    }

    public function dismiss()
    {
        //TODO 解散一个用户组
    }
}
