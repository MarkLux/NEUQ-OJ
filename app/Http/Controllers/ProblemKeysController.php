<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-21
 * Time: 下午7:37
 */

namespace NEUQOJ\Http\Controllers;


use Illuminate\Http\Request;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Services\ProblemKeysService;
use NEUQOJ\Services\RoleService;
use NEUQOJ\Services\UserService;

class ProblemKeysController extends Controller
{
    public function addProblemSolution(Request $request,UserService $userService,ProblemKeysService $problemKeysService,RoleService $roleService)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'problemId' => 'required|max:45',
            'user'=>'required',
            'title'=>'required|max:100',
            'key'=>'required'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        $user = $request->user;
        //判断是否是出题人

        $problem = $userService->getUserById($user->id);

        if($problem->creator_id != $user->id)
            if($roleService->hasRole($user->id,'admin'))//如果是管理员就不说了
            throw new NoPermissionException();
        //整合数据

        $data = [
            'problem_id'=>$request->problemId,
            'title'=>$request->title,
            'key'=>$request->key,
            'author_id'=>$user->id,
            'author_name'=>$user->name
        ];

        if($problemKeysService->addProblemKey($data))
            return response()->json(
                [
                    'code'=> 0
                ]
            );
    }

    public function deleteProblemKey(Request $request,ProblemKeysService $problemKeysService,RoleService $roleService,UserService $userService)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'problemId' => 'required|max:45',
            'user'=>'required',
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        $user = $request->user;
        //判断是否是出题人

        $problem = $userService->getUserById($user->id);

        if($problem->creator_id != $user->id)
            if($roleService->hasRole($user->id,'admin'))//如果是管理员就不说了
                throw new NoPermissionException();

        if($problemKeysService->deleteProblemKey($request->problemId))
            return response()->json(
                [
                    'code'=>0
                ]
            );
    }

    public function updateProblemKey(Request $request)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'problemId' => 'required|max:45',
            'user'=>'required',
            'title'=>'required',
            'key'=>'required'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        $user = $request->user;

    }

}