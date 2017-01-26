<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\RegisterErrorException;
use NEUQOJ\Exceptions\UserIsActivatedException;
use NEUQOJ\Exceptions\UserLockedException;
use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Http\Requests;
use NEUQOJ\Repository\Models\User;
use NEUQOJ\Services\TokenService;
use NEUQOJ\Services\UserService;
use Illuminate\Support\Facades\Response;
use NEUQOJ\Services\VerifyService;

class UserController extends Controller
{
    public function register(Request $request,UserService $userService,VerifyService $verifyService)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|max:100',
            'email' => 'required|email|max:100',
            'mobile' => 'required|max:45',
            'password' => 'required|confirmed|min:6|max:20',
            'school' => 'string|max:100'
        ]);

        if($validator->fails()) {
            $error = $validator->getMessageBag()->all();
            throw new FormValidatorException($error);
        }

        $userId = $userService->register($request->all());

        $user = $userService->getUserById($userId,['id','name','email']);

        $verifyService->sendVerifyEmail($user);

        return response()->json([
            'code' => 0,
            'user_id' => $userId
        ]);
    }

    public function active(Request $request,UserService $userService,VerifyService $verifyService)
    {
        $validator = Validator::make($request->all(),[
            'verify_code' => 'required',
            'user_id' => 'required'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        if(!$verifyService->activeUserByEmailCode($request->user_id,$request->verify_code))
            throw new InnerError("Fail to active User: ".$request->user_id);

        $data = $userService->loginUser($request->user_id,$request->ip());

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function resendActiveMail(Request $request,UserService $userService,VerifyService $verifyService)
    {
        //重新发送邮件，应该检测上次发送邮件的时长，防止有人恶意重复访问此接口损耗服务器性能

        $validator = Validator::make($request->all(),[
            'user_id' => 'required'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $user = $userService->getUserById($request->user_id,['id','name','status','email']);

        if($user == null) throw new UserNotExistException();
        elseif($user->status == -1) throw new UserLockedException();
        elseif($user->status == 1) throw new UserIsActivatedException();

        if(!$verifyService->sendVerifyEmail($user))
            throw new InnerError('Fail to Send Email');

        return response()->json([
            'code' => 0
        ]);
    }

    public function login(Request $request,UserService $userService,TokenService $tokenService)
    {
        $validator = Validator::make($request->all(),[
            'identifier' => 'required|max:100',
            'password' => 'required|min:6'
        ]);

        if($validator->fails()) {
            $error = $validator->getMessageBag()->all();
            throw new FormValidatorException($error);
        }

        $user = $userService->login($request->all());

        $tokenStr = $tokenService->makeToken($user->id,$request->ip());

        return response()->json([
            'code' => 0,
            'data' => [
                'user' => $user,
                'token' => $tokenStr
            ]
        ]);
    }

    public function getUserInfo(Request $request)
    {
        return response()->json([
            'code' => 0,
            'data' => $request->user
        ]);
    }

    public function getUser(Request $request,UserService $userService)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'integer|max:20',
            'name' => 'string|max:100',
            'email' => 'email|max:100',
            'mobile' =>'string|max：45',
            'school' =>'string|max:100',
        ]);

        if($validator->fails()) {
            $error = $validator->getMessageBag()->all();
            throw new FormValidatorException($error);
        }

        $id = $request->get('id');

        if($id != null)
            $user = $userService->getUserById($id);
        else
            $user = $userService->getUserByMult($request->all());

        return response()->json([
            'code' => 0,
            'user' => $user
        ]);
    }

    public function getUsers(Request $request,UserService $userService)
    {
        $users = $userService->getUsers($request->all());
        return response()->json([
            'code' => 0,
            'users' => $users
        ]);
    }

    public function updateUser(Request $request,UserService $userService)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'integer',
            'email' => 'email|max:100',
            'mobile' => 'string|max:50',
            'school' =>'string|max:100',
            'signature' => 'string|max:512',
        ]);

        if($validator->fails()) {
            $error = $validator->getMessageBag()->all();
            throw new FormValidatorException($error);
        }

        $id = $request->get('id');
        $email = $request->get('email');
        $data = [
            'mobile' => $request->get('mobile'),
            'school' => $request->get('school'),
            'signature' => $request->get('signature')
        ];

        if($id != null) {
            $flag = $userService->updateUserById($id,$data);
        } elseif ($email != null) {
            $flag = $userService->updateUser(['email' => $email],$data);
        }

        if($flag) {
            return response()->json([
                'code' => 0,
            ]);
        }
    }

    public function lockUser(UserService $userService,$id)
    {
        if($userService->lockUser($id))
            return response()->json([
                'code' => 0
            ]);
    }

    public function unlockUser(UserService $userService,$id)
    {
        if($userService->unlockUser($id))
            return response()->json([
                'code' => 0
            ]);
    }

    public function logout(Request $request,TokenService $tokenService)
    {
        $tokenService->destoryToken($request->user->id);

        return response()->json([
            'code' => 0
        ]);
    }
}
