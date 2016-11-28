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

class TestController extends Controller
{

    public function register(Request $request,UserRepository $userRepository)
    {



        $status_email = app('user')->hasUserEmail($request,$userRepository);
        $status_mobile = app('user')->hasUserMobile($request,$userRepository);
        if(($status_email||$status_mobile)==0)
            throw new UserExistedException();

        app('user')->ruleRegisterData($request);

        app('user')->insert($request,$userRepository);

        return response()->json([
            'code' => '0'
        ]);
    }


}