<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-20
 * Time: 下午10:36
 */

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;
use NEUQOJ\Exceptions\UserExistedException;
use NEUQOJ\Exceptions\ValidatorException;
use NEUQOJ\Http\Controllers\Controller;
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

            throw new ValidatorException($data);
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

    public function login(Request $request,UserRepository $userRepository)
    {

    }
}