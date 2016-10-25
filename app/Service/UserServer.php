<?php

/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-10-24
 * Time: 下午10:35
 */
namespace NEUQOJ\Service;

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

class UserServer
{
    public function __construct(){
    }

    public function hasUserEmail(Request $request,UserRepository $userRepository)
    {

        $user = $userRepository->getBy('email',$request->email)->first();


        if($user!=null)
            return false;
        else
            return true;
    }

    public  function  hasUserMobile(Request $request,UserRepository $userRepository)
    {
        $user = $userRepository->getBy('mobile',$request->mobile)->first();

        if($user!=null)
            return false;
        else
            return true;
    }

    public function ruleRegisterData(Request $request)
    {
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
    }

    public function insert(Request $request,UserRepository $userRepository)
    {
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


    }
}