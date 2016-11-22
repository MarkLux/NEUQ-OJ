<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-11-22
 * Time: 下午10:15
 */

namespace NEUQOJ\Http\Controllers;


use Illuminate\Http\Request;
use League\Flysystem\Exception;
use NEUQOJ\Exceptions\RoleExistedException;
use NEUQOJ\Services\RoleService;

class RoleController extends Controller
{

    public function createRole(RoleService $roleService,Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|max:30',
            'privilege'=>'required',
            'description'=>'required|max:100'
        ]);


        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        if($roleService->roleExisted($request->get('role')))
            throw new RoleExistedException();

        $data = array(
            'role'=>$request->get('role'),
            'privilege'=>$request->get('privilege'),
            'description'=>$request->get('description'),
        );
        if($roleService->createRole($data))
            return response()->json([
                'code' => '0'
            ]);
    }
}