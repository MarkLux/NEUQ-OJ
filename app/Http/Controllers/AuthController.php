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
        $user = $userRepository->getBy('email',$request->email)->first();

        if($user!=null)
            throw new UserExistedException();

        $user = $userRepository->getBy('mobile',$request->mobile)->first();

        if($user!=null)
            throw new UserExistedException();

        //可以考虑修改错误信息为自定义中文
        $validator = Validator::make($request->all(),[
            'name' => 'required|max:100',
            'email' => 'required|email|max:100',
            'mobile' => 'required|max:45',
            'password' => 'required|confirmed|min:6|max:20'
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
        $validator = Validator::make($request->all(),[
            'name' => 'required|max:100',
            'identifier' => 'required|max:100',
            'password' => 'required|min:6'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();

            throw new FormValidatorException($data);
        }

        //正则验证登陆方式

        $patternEmail = '/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/';
        $patternMobile ='/(13\d|14[57]|15[^4,\D]|17[678]|18\d)\d{8}|170[059]\d{7}/';
        if(preg_match($patternEmail,$request->identifier))
        {
            $user = $userRepository->getBy('email',$request->identifier)->first();
        }
        elseif (preg_match($patternMobile,$request->identifier))
        {
            $user = $userRepository->getBy('mobile',$request->identifier)->first();
        }
        else
            throw new FormValidatorException(['Wrong Param']);

        if($user == null)
            throw new UserNotExistException();

//        dd($user->password);

        if(!Hash::check($request->password,$user->password))
            throw new PasswordErrorException();

        //为该登陆用户生成token

        $token = $tokenRepository->getBy('user_id',$user->id)->first();

        if($token == null)
        {
            $tokenStr = md5(uniqid());
            $time = time();
            $data = [
                'user_id' => $user->id,
                'token' => $tokenStr,
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
            $tokenStr = md5(uniqid());
            $data = [
                'token' => $tokenStr,
                'updated_at' => $time,
                'expires_at' => $time+1728000000
            ];
            $tokenRepository->update($data,$token->id);
        }

        return response()->json([
            'code' => '0',
            'data' => [
                'user' => $user,
                'token' => $tokenStr
            ]
        ]);

    }

}