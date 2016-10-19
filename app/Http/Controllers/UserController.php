<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-19
 * Time: 下午7:48
 */

namespace NEUQOJ\Http\Controllers;

use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\UserExistedException;
use NEUQOJ\Http\Controllers\Controller;
use NEUQOJ\Http\Request;
use NEUQOJ\Repository\Eloquent\UserRepository;


class UserController extends Controller
{
    public function login(Request $request,UserRepository $userRepository)
    {
       $user = $userRepository->getBy('mobile',$request->mobile);
       if($user != null)
           throw new UserExistedException();



       $user = array(
           'name' => $request->name,
           'email' => $request->email,
           'mobile' => $request->mobile,
           'password' => Utils::encryption($request->password),
           'school' => $request->school
       );

       $userRepository->insert($user);



    }
}