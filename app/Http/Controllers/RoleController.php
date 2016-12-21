<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-22
 * Time: 下午10:15
 */

namespace NEUQOJ\Http\Controllers;


use Illuminate\Http\Request;
use League\Flysystem\Exception;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\PrivilegeNotExistException;
use NEUQOJ\Exceptions\RoleExistedException;
use NEUQOJ\Exceptions\RoleNotExistException;
use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Services\PrivilegeService;
use NEUQOJ\Services\RoleService;
use NEUQOJ\Services\UserService;
use Validator;
class RoleController extends Controller
{

    public function test(Request $request)
    {
       dd($request->user['id']);
    }

    public function createRole(RoleService $roleService,Request $request,PrivilegeService $privilegeService)
    {
        /*
         * 表单验证
         */
        $validator = Validator::make($request->all(), [
            'role' => 'required|max:30',
            'privilege'=>'required',
            'description'=>'required|max:100',
            'user'=>'required'
        ]);


        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        
        /*
         * 判断要增加的角色是否存在
         */
        if($roleService->roleExisted($request->get('role')))
            throw new RoleExistedException();

        /*
         * 判断操作者是否具有对应权限
         */
        //dd($request->user['id']);
        if(!($privilegeService->hasNeededPrivilege('operate-role',$request->user['id'])))
            throw new PrivilegeNotExistException();


        $data = array(
            'role'=>$request->get('role'),
            'privilege'=>$request->get('privilege'),
            'description'=>$request->get('description'),
        );
        if($roleService->createRole($data))
            return response()->json([
                'code' => 0
            ]);
    }

    public function deleteRole(Request $request,RoleService $roleService,PrivilegeService $privilegeService)
    {
        /*
         * 表单验证
         */
        $validator = Validator::make($request->all(), [
            'role' => 'required|max:30',
            'user'=>'required'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        /*
         * 判断要删除的角色是否存在
         */

        if(!($role = $roleService->roleExisted($request->get('role'))))
            throw new RoleNotExistException();
            $roleId = $role->id;
        /*
        * 判断操作者是否具有对应权限
        */
        if(!($privilegeService->hasNeededPrivilege('operate-role',$request->user['id'])))
            throw new PrivilegeNotExistException();


        if($roleService->deleteRole($roleId))
            return response()->json([
                'code' => 0
            ]);
    }

    /*
     * 申请得过中间件　相应角色才可以操作
     */
    public function giveRoleTo(Request $request,RoleService $roleService,PrivilegeService $privilegeService)
    {


        $validator = Validator::make($request->all(), [
            'user'=>'required',
            'user_id' => 'required',
            'role'=>'required',
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }


        $role = $request->role;

        /*
         * 判断给予的角色是否存在
         */
        if(!($roleService->roleExisted($role)))
            throw new RoleNotExistException();

        /*
         * 判断给予人是否有对应的权限
         */
        if(!($privilegeService->hasNeededPrivilege('operate-role',$request->user['id'])))
            throw new PrivilegeNotExistException();


         if($roleService->giveRoleTo($request->user_id,$role))
         {
             return response()->json(
                 [
                     'code'=> 0
                 ]
             );
         }

    }

    public function updateRole(Request $request,RoleService $roleService)
    {
        //
    }
}