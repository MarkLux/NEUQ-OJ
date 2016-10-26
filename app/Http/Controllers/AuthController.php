<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-20
 * Time: 下午10:36
 */

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\UserExistedException;
use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Services\TokenService;
use NEUQOJ\Services\UserService;
use Validator;

class AuthController extends Controller
{

    public function register(Request $request, UserService $userService)
    {
        //可以考虑修改错误信息为自定义中文
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'email' => 'required|email|max:100',
            'mobile' => 'required|max:45',
            'password' => 'required|confirmed|min:6|max:20'
        ]);

        if ($validator->fails()) {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        //正则检查手机号
        if (!Utils::IsMobile($request->mobile)) {
            throw new FormValidatorException(["Valid Moblie Number"]);
        }

        //手机和邮箱都应该检查
        if ($userService->isUserExist('mobile', $request->mobile) || $userService->isUserExist('email', $request->email))
            throw new UserExistedException();

        $user = [
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => bcrypt($request->password),
            'school' => $request->school ? $request->school : "Unknown"
        ];

        /*
         *缺少邮箱和手机验证检查
          */

        $userService->createUser($user);

        return response()->json([
            'code' => '0'
        ]);
    }

    public function login(Request $request, TokenService $tokenService, UserService $userService, UserRepository $userRepository)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'identifier' => 'required|max:100',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            $data = $validator->getMessageBag()->all();

            throw new FormValidatorException($data);
        }

        //正则验证登陆方式

        if (Utils::IsEmail($request->identifier)) {
            $user = $userService->getUser($request->identifier, 'email');
        } elseif (Utils::IsMobile($request->identifier)) {
            $user = $userService->getUser($request->identifier, 'mobile');
        } else
            throw new FormValidatorException(['Invalid Indentifier Format']);

        if ($user == null)
            throw new UserNotExistException();

        if (!Hash::check($request->password, $user->password))
            throw new PasswordErrorException();

        //为该登陆用户生成token

        $tokenStr = $tokenService->makeToken($user->id, $request->ip());

        return response()->json([
            'code' => '0',
            'data' => [
                'user' => $user,
                'token' => $tokenStr
            ]
        ]);

    }

}
