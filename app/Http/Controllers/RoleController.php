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

    public function test(PrivilegeService $privilegeService)
    {
        return $privilegeService->getRolePrivilege(1);
    }

    public function createRole(RoleService $roleService,Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|max:30',
            'privilege'=>'required',
            'description'=>'required|max:100'
        ]);


        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        if($roleService->roleExisted($request->get('role')))
            throw new RoleExistedException();

        $data = array(
            'role'=>$request->get('role'),
            'privilege'=>$request->get('privilege'),
            'description'=>$request->get('description'),
        );
        if($roleService->createRole($data))
            return response()->json([
                'code' => '0'
            ]);
    }

    public function deleteRole(Request $request,RoleService $roleService)
    {

        $roleId = $request->roleId;
        if($roleService->deleteRole($roleId))
            return response()->json([
                'code' => '0'
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

        /*
         * 判断给予人是否有对应的权限
         */
        if(!($privilegeService->hasNeededPrivilege('admin',$request->user->id)))
            throw new PrivilegeNotExistException();

        $role = $request->role;

        /*
         * 判断给予的角色是否存在
         */
        if(!($roleService->roleExisted($role)))
            throw new RoleNotExistException();


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

        $roleService->updateRole();
    }
}