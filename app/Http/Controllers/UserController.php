<?php

namespace NEUQOJ\Http\Controllers;

use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\UserExistedException;
use NEUQOJ\Http\Controllers\Controller;
use NEUQOJ\Http\Request;
use NEUQOJ\Repository\Eloquent\UserRepository;
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
        return response()->json([
            'code'  =>  0,
            'data'  =>  [
                'user'  =>  $user
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
        $data = [
            'name'=> $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'school' =>$request->school,
            'signature' => $request->signature
        ];

        $userService->updateUser($data,$user->id);
    }

    /**
     * 封禁用户
     *
     * @param Request $request
     */
    public function banUser(Request $request,UserService $userService)
    {
        $id = $request->id;
        $user = $userService->getUser($id);
        $user->status = -1;
    }


}