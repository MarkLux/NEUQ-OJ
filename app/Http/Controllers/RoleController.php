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
use NEUQOJ\Exceptions\RoleExistedException;
use NEUQOJ\Exceptions\RoleNotExistException;
use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Services\RoleService;
use NEUQOJ\Services\UserService;
use Validator;
class RoleController extends Controller
{

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
    public function giveRoleTo(Request $request,RoleService $roleService,UserService $userService)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'role'=>'required',
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }
        $role = $request->role;

        if(!($roleService->roleExisted($role)))
            throw new RoleNotExistException();


        if(!($userService->isUserExist('mobile', $request->mobile)))
            throw new UserNotExistException();

         if($roleService->giveRoleTo($request->user_id,$role))
         {
             return response()->json(
                 [
                     'code'=> 0
                 ]
             );
         }

    }
}