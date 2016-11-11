<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;

use NEUQOJ\Exceptions\RegisterErrorException;
use NEUQOJ\Http\Requests;
use NEUQOJ\Repository\Models\User;
use NEUQOJ\Services\UserService;

class UserController extends Controller
{
    public function register(Request $request,UserService $userService)
    {
        $data = $request->all();
        if(!$userService->register($data))
            throw new RegisterErrorException();
        return response()->json([
            'code' => '0',
        ]);
    }

    public function login(Request $request,UserService $userService)
    {

    }
}
