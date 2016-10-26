<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-10-26
 * Time: 下午7:52
 */

namespace NEUQOJ\Http\Controllers;

use NEUQOJ\Http\Controllers\Controller;
use NEUQOJ\Http\Requests\Request;

class PrivilegeController extends Controller
{
    public function __construct()
    {

    }
/*
 * 提交教师申请
 */
    public function applyTeacherTo(Request $request)
    {

        $user = $request->user->id;



    }

    public function confirmTeacherApply(Request $request)
    {

    }
}