<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;
use NEUQOJ\Services\UserService;
use NEUQOJ\Http\Requests;

class AdminController extends Controller
{
    public function lockUser(UserService $userService,$id)
    {
        if($userService->lockUser($id))
            return response()->json([
                'code' => '0'
            ]);
    }

    public function unlockUser(UserService $userService,$id)
    {
        if($userService->unlockUser($id))
            return response()->json([
                'code' => '0'
            ]);
    }
}
