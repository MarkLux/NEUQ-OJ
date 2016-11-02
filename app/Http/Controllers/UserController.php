<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\UserExistedException;
use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Http\Controllers\Controller;
use Illuminate\Http\Request;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Repository\Models\User;
use NEUQOJ\Services\UserService;


class UserController extends Controller
{
    public function getUserInfo(Request $request)
    {
        $user = $request->user;
        //dd($user);
        return response()->json([
            'code' => '0',
            'data' => [
                'user' => $user
            ]
        ]);
    }

    public function updateUserInfo(Request $request,UserService $userService)
    {
        $user = $request->user;
        $data = [
            'name'=> $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'school' => $request->school,
            'signature' => $request->signature,
        ];
        $userService->updateUser($data,$user->id);
        return response()->json([
            'code' => '0',
        ]);
    }

    public function lockUser(Request $request,UserService $userService)
    {
        $id = $request->id;
        if(!$userService->lockUser($id)) {
            throw new UserNotExistException();
        }else {
            return response()->json([
                'code' => '0',
            ]);
        }
    }

    public function unlockUser(Request $request,UserService $userService)
    {
        $id = $request->id;
        if(!$userService->unlockUser($id)) {
            throw new UserNotExistException();
        }else {
            return response()->json([
                'code' => '0',
            ]);
        }
    }

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
            'code' => '0',
        ]);
    }

    public function forgetPassword()
    {

    }
}
