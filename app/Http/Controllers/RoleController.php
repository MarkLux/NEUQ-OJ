<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-22
 * Time: 下午10:15
 */

namespace NEUQOJ\Http\Controllers;


use NEUQOJ\Http\Requests\Request;
use NEUQOJ\Services\RoleService;

class RoleController extends Controller
{

    public function createRole(Request $request,RoleService $roleService)
    {
        return $roleService->createRole($request);
    }
}