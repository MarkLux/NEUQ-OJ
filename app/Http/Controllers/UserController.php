<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\UserExistedException;
use NEUQOJ\Http\Controllers\Controller;
use Illuminate\Http\Request;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Repository\Models\User;
use NEUQOJ\Services\UserService;


class UserController extends Controller
{
    /**
     * 获取用户个人信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserInfo(Request $request)
    {
        $user = $request->user;
        //dd($user);
        return response()->json([
            'code' => 0,
            'data' => [
                'user' => $user
            ]
        ]);
    }

    /**
     * 修改用户信息
     *
     * @param Request $request
     * @param UserService $userService
     */
    public function updateUserInfo(Request $request,UserService $userService)
    {
        $user = $request->user;
        //dd($user);
        $data = [
            'name'=> $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'school' => $request->school,
            'signature' => $request->signature,
        ];

        $userService->updateUser($data,$user->id);
        return response()->json([
            'code' => 0,
        ]);
    }

    /**
     * 封禁用户
     *
     * @param Request $request
     */
    public function banUser(Request $request,UserService $userService)
    {
        $id = $request->id;
        $userService->banUser($id);
        return response()->json([
            "code" => 0
        ]);
    }


    /**
     * 修改密码
     *
     * @param Request $request
     * @param UserService $userService
     * @return \Illuminate\Http\JsonResponse
     * @throws PasswordErrorException
     */
    public function updatePassword(Request $request,UserService $userService)
    {
        $user = $request->user;
        if(!Hash::check($request->oldpassword,$user->password)) {
            throw new PasswordErrorException();
        }

        $data = [
            "password" => bcrypt($request->newpassword)
        ];
        $userService->updateUser($data,$user->id);
        return response()->json([
            'code' => 0,
        ]);
    }
}
