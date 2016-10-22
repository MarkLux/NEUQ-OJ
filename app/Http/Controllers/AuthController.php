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
use NEUQOJ\Http\Controllers\Controller;
use NEUQOJ\Repository\Eloquent\UserRepository;

class AuthController extends Controller
{
    public function register(Request $request,UserRepository $userRepository)
    {
        $user = $userRepository->getBy('email',$request->email);


        if($user->all()!=null)
            throw new UserExistedException();

//        $this->validate($request,[
//            'name' => 'required|max:100',
//            'email' => 'required|email|max:100',
//            'mobile' => 'required|max:45',
//            'password' => 'required|confirmed|min:6'
//        ]);

        $user= [
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => bcrypt($request->password),
            'school' => $request->school
        ];


        /*
         *Need VerifyCode check here...
          */

//        dd($user);

        $userRepository->insert($user);

        return response()->json([
            'code' => '0'
        ]);
    }
}