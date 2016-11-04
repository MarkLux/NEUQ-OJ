<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-10-28
 * Time: 下午8:13
 */

namespace NEUQOJ\Services;


use NEUQOJ\Http\Requests\Request;
use NEUQOJ\Repository\Eloquent\ApplyRepository;
use NEUQOJ\Repository\Eloquent\UserRepository;

class ApplyService
{

    public function __construct()
    {

    }

    public function CreateApply($data,ApplyRepository $applyRepo)
    {
        return $applyRepo->insert($data);
    }


    /*
     * 寻找对应角色
     */
    public function FindRole($role,UserRepository $userRepository)
    {
        return $userRepository->getBy('role','>=',$role);
    }
    public function updateRole($status,$type,$id,UserRepository $userRepository)
    {
        if($status)
            //私信(待做）
            return response()->json([
                'code' => '1'
            ]);
        else
        {
            if($type == 1)
            $data = [
                'role'=>1
            ];
            elseif ($type == 2)
                $data = [
                    'role'=>1
                ];
            if($userRepository->update($data,$id)) {
                //私信（待做）
                return response()->json([
                    'code' => '0'
                ]);
            }
        }
    }
}