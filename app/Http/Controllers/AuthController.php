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
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\UserExistedException;
use NEUQOJ\Exceptions\UserNotExistException;
use NEUQOJ\Http\Controllers\Controller;
use NEUQOJ\Repository\Eloquent\TokenRepository;
use NEUQOJ\Repository\Eloquent\UserRepository;
use Validator;

class AuthController extends Controller
{

    public function register(Request $request,UserRepository $userRepository)
    {

        //手机和邮箱都应该检查
        $user = $userRepository->getBy('email',$request->email);

        if($user->all()!=null)
            throw new UserExistedException();

        $user = $userRepository->getBy('mobile',$request->mobile);

        if($user->all()!=null)
            throw new UserExistedException();

        //可以考虑修改错误信息为自定义中文
        $validator = Validator::make($request->all(),[
            'name' => 'required|max:100',
            'email' => 'required|email|max:100',
            'mobile' => 'required|max:45',
            'password' => 'required|confirmed|min:6'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();

            throw new FormValidatorException($data);
        }

        $user= [
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => bcrypt($request->password),
            'school' => $request->school
        ];

        /*
         *邮件和短信验证逻辑....
          */

        $userRepository->insert($user);

        return response()->json([
            'code' => '0'
        ]);
    }

    public function login(Request $request,UserRepository $userRepository,TokenRepository $tokenRepository)
    {
        //先验证输入数据,method是登陆方式，可选手机或者邮箱
        $validator = Validator::make($request->all(),[
            'login_method' => 'required',
            'name' => 'required|max:100',
            'identifier' => 'required|max:100',
            'password' => 'required|min:6'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();

            throw new FormValidatorException($data);
        }

        if($request->login_method == 'email')
        {
            $user = $userRepository->getBy('email',$request->identifier)->first();
        }
        elseif ($request->login_method == 'mobile')
        {
            $user = $userRepository->getBy('mobile',$request->indentifier)->first();
        }
        else
            throw new FormValidatorException(['Wrong Param For Method']);

        if($user == null)
            throw new UserNotExistException();

//        dd($user->password);

        if(!Hash::check($request->password,$user->password))
            throw new PasswordErrorException();

        //为该登陆用户生成token

        $token = $tokenRepository->getBy('user_id',$user->id)->first();

        if($token == null)
        {
            $time = time();
            $data = [
                'user_id' => $user->id,
                'token' => md5(uniqid()),
                'created_at' => $time,
                'updated_at' => $time,
                'expires_at' => $time + 1728000000,
                'ip' => $request->ip()
            ];
            $tokenRepository->insert($data);
        }
        else
        {
            $time = time();
            $data = [
                'token' => md5(uniqid()),
                'updated_at' => $time,
                'expires_at' => $time+1728000000
            ];
            $tokenRepository->update($data,$token->id);
        }

        return response()->json([
            'code' => '0',
            'data' => [
                'user' => $user,
                'token' => $token->token
            ]
        ]);

    }

}