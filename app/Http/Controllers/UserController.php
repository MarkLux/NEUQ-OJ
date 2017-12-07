<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Mews\Captcha\Facades\Captcha;
use NEUQOJ\Exceptions\Captcha\CaptchaNotMatchException;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\UserIsActivatedException;
use NEUQOJ\Exceptions\UserLockedException;
use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Facades\Permission;
use NEUQOJ\Http\Requests;
use NEUQOJ\Repository\Models\User;
use NEUQOJ\Services\CaptchaService;
use NEUQOJ\Services\TokenService;
use NEUQOJ\Services\UserService;
use Illuminate\Support\Facades\Response;
use NEUQOJ\Services\VerifyService;

class UserController extends Controller
{
    private $userService;
    private $verifyService;
    private $tokenService;

//    private $captchaService;

    public function __construct(UserService $userService, VerifyService $verifyService, TokenService $tokenService)
    {
        $this->userService = $userService;
        $this->verifyService = $verifyService;
        $this->tokenService = $tokenService;
    }

    public function getCaptcha()
    {
        $url = Captcha::src();

        return response()->json([
            'code' => 0,
            'data' => [
                'url' => $url
            ]
        ]);
    }

    //验证码的注册必须让前端带上cookie才能保持同一个会话
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'email' => 'required|email|max:100',
            'mobile' => 'required|max:45',
            'password' => 'required|confirmed|min:6|max:20',
            'school' => 'string|max:100',
//            'captcha' => 'required|captcha'
        ]);

        if ($validator->fails()) {
            $error = $validator->getMessageBag()->all();
            throw new FormValidatorException($error);
        }

        $userId = $this->userService->register($request->all());

        //正式上线时，激活这里的邮件验证服务

//        $user = $this->userService->getUserById($userId, ['id', 'name', 'email']);

//        $this->verifyService->sendVerifyEmail($user);

        return response()->json([
            'code' => 0,
            'data' => [
                'user_id' => $userId
            ]
        ]);
    }

    public function active(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verify_code' => 'required'
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $userId = $this->verifyService->activeUserByEmailCode($request->verify_code);

        if ($userId == -1)
            throw new InnerError("Fail to active User: " . $request->user_id);

        $data = $this->userService->loginUser($userId, $request->ip());

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function resendActiveMail(Request $request)
    {
        //重新发送邮件，应该检测上次发送邮件的时长，防止有人恶意重复访问此接口损耗服务器性能

        $validator = Validator::make($request->all(), [
            'email' => 'required'
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $user = $this->userService->getUserBy('email',$request->email, ['id', 'name', 'status', 'email']);

        if ($user == null)
            throw new UserNotExistException();
        else if ($user->status == -1)
            throw new UserLockedException();
        else if ($user->status == 1)
            throw new UserIsActivatedException();

        if (!$this->verifyService->resendVerifyEmail($user))
            throw new InnerError('Fail to Send Email');

        return response()->json([
            'code' => 0
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|max:100',
            'password' => 'required|min:6|max:20'
        ]);

        if ($validator->fails()) {
            $error = $validator->getMessageBag()->all();
            throw new FormValidatorException($error);
        }

        $user = $this->userService->login($request->all());

        $tokenStr = $this->tokenService->makeToken($user->id, $request->ip());

        $role = Permission::getUserRole($user->id);

        if ($role != null) {
            $role = $role->role_name;
        }else{
            $role = "user";
        }

        return response()->json([
            'code' => 0,
            'data' => [
                'user' => $user,
                'token' => $tokenStr,
                'role' => $role
            ]
        ]);
    }

    //获取当前登录用户的资料

    public function getCurrentUserInfo(Request $request)
    {
        $role = Permission::getUserRole($request->user->id);

        if ($role != null) {
            $role = $role->role_name;
        }else{
            $role = "user";
        }

        return response()->json([
            'code' => 0,
            'data' => $request->user,
            'role' => $role
        ]);
    }

    //获取指定用户的资料

    public function getUserInfo(Request $request, int $id)
    {
        $role = Permission::getUserRole($id);

        if ($role != null) {
            $role = $role->role_name;
        }else{
            $role = "user";
        }

        $user = $this->userService->getUserById($id, ['id', 'submit', 'solved', 'name', 'school', 'signature', 'created_at']);

        if ($user == null) throw new UserNotExistException();

        return response()->json([
            'code' => 0,
            'data' => $user,
            'role' => $role
        ]);
    }

    //当前用户更新自己个人资料的接口

    public function updateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|min:0|max:100',
            'school' => 'string|min:0|max:100',
            'signature' => 'string|min:0|max:512'
        ]);

        if ($validator->fails()) {
            $error = $validator->getMessageBag()->all();
            throw new FormValidatorException($error);
        }

        $id = $request->user->id;

        $name = $request->input('name', null);
        $school = $request->input('school', null);
        $signature = $request->input('signature', null);

        $data = [];

        if ($name != null) $data['name'] = $name;
        if ($school != null) $data['school'] = $school;
        if ($signature != null) $data['signature'] = $signature;


        if (!empty($data))
            if (!$this->userService->updateUserById($id, $data))
                throw new InnerError("Fail to update User");

        return response()->json([
            'code' => 0
        ]);
    }
    public function multiUpdateUser(Request $request)
    {
        $data=$request->input('data');
        foreach ($data as $id => $name)
        {
            $datas=[];
            $datas['name']=$name;
            $this->userService->updateUserById($id,$datas);
        }
        return response()->json([
            'code' => 0
        ]);
    }
    public function resetPasswordByOld(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'old_password' => 'required',
            'new_password' => 'required|confirmed|string|min:6|max:20'
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        if (!$this->userService->resetPasswordByOldPass($request->user_id, $request->old_password, $request->new_password))
            throw new InnerError("Fail to reset password");

        return response()->json([
            'code' => 0
        ]);
    }

    //TODO 考虑这里是否需要验证码防止机器人攻击⬇

    public function sendForgotPasswordEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
//            'captcha' => 'required|captcha'
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        if (!$this->verifyService->sendResetPasswordEmail($request->email))
            throw new InnerError("Fail to send check email");

        return response()->json([
            'code' => 0
        ]);
    }

    public function resetPasswordByVerifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:6|max:20|confirmed',
            'verify_code' => 'required|string'
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());


        if (!$this->userService->resetPasswordByVerifyCode($request->verify_code, $request->new_password))
            throw new InnerError('Reset Failed!');

        return response()->json([
            'code' => 0
        ]);
    }

    public function logout(Request $request, TokenService $tokenService)
    {
        $tokenService->destoryToken($request->user->id);

        return response()->json([
            'code' => 0
        ]);
    }

    public function checkToken(Request $request,TokenService $tokenService)
    {
        $checkRes = $tokenService->verifyToken($request->header('token'),$request->all());
        if ($checkRes instanceof View) {
            return $checkRes;
        }
    }

    public function getRankList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'size' => 'integer|min:1|max:50',
            'scope' => 'string'
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $scope = $request->input('scope', 'total');
        $page = $request->input('page', 1);
        $size = $request->input('size', 20);

//        dd($scope);

        return response()->json([
            'code' => 0,
            'data' => $this->userService->getRankList($scope, $page, $size)
        ]);

    }
}
