<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-10-28
 * Time: 下午8:34
 */

namespace NEUQOJ\Http\Controllers;

use NEUQOJ\Http\Controllers\Controller;
use Illuminate\Http\Request;
use NEUQOJ\Repository\Eloquent\UserRepository;
use NEUQOJ\Services\ApplyService;
use NEUQOJ\Repository\Eloquent\ApplyRepository;
class ApplyController extends Controller
{
    /*
     * 统一将申请提交至此
     * 存表并标注类型
     */
    public function getApply(Request $request,ApplyService $applyService,ApplyRepository $applyRepo,UserRepository $userRepository)
    {
        $data = [
        'id' => $request->user->id,
        'name' => $request->user->name,
        'type' => $request->type
        ];
        /*
         * type 2为申请管理员 1为申请老师
         */
        $applyService->CreateApply($data, $applyRepo);

        //$this->handelApply($request,$applyService,$userRepository);
        return response()->json([
            'code' => 0
        ]);
    }
    /*
     * 处理申请
     * 根据类型 私信老师，管理员
     * 发送applies表的所有内容
     */
    public function handelApply(Request $request,ApplyService $applyService, UserRepository $userRepository)
    {
        $type = $request->type;
        if($type == 1)
            $role=1;
        elseif ($type == 2)
            $role = 2;
        $hander = $applyService->FindRole($role,$userRepository);


    }
    /*
     * 更新user表 role字段
     * Request 包括 申请内容，处理人信息
     */
    public function confirmRoleApply(Request $request,ApplyService $applyService,UserRepository $userRepository)
    {
        $id = $request->id;
        $type = $request->type;
        $status = $request->status;
        $hander = $request->user->id;
        $applyService->updateRole($status,$type,$id,$userRepository);
    }
}