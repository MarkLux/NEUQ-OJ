<?php
/**
 * Created by PhpStorm.
 * User: NEUQer
 * Date: 17/1/2
 * Time: 下午8:45
 */

namespace NEUQOJ\Http\Controllers;




use Dotenv\Validator;
use Illuminate\Http\Request;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Services\MessageService;
use NEUQOJ\Services\PrivilegeService;
use NEUQOJ\Services\RoleService;

class MessageController extends Controller
{
    public function sendMessage(Request $request,MessageService $messageService)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'from_id' => 'required',
            'to_id'=>'required',
            'from_name'=>'required|max:100',
            'to_name'=>'required|max:100',
            'contents'=>'required'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }
        $data = [
            'from_name'=>$request->from_name,
            'to_name'=>$request->to_name,
            'content'=>$request->contents
        ];

        if($messageService->sendMessage($request->from_id,$request->to_id,$data))
            return response()->json(
                [
                    'code'=>0
                ]
    );
    }
    public function getUserMessages(Request $request,MessageService $messageService)
    {
        $validator = Validator::make($request->all(),[
           'page'=>'required|min:1',
            'size'=>'required|min:1',
            'user_id'=>'required'
        ]);
        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }
        $message = null;
        //只取了发送人信息和内容
        $message = $messageService->getUserMessages($request->user_id,$request->page,$request->size,
            ['id','from_id','from_name','content']);

            return response()->json(
                [
                    'code'=>0,
                    'data'=>$message
                ]
            );


    }

    public function getUserUnreadMessages(Request $request,MessageService $messageService)
    {
        $validator = Validator::make($request->all(),[
            'page'=>'required|min:1',
            'size'=>'required|min:1',
            'user_id'=>'required'
        ]);
        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        $message =null;
        $message = $messageService->getUnreadMessages($request->user_id,$request->page,$request->size,
            ['id','from_id','from_name','content']);

            return response()->json(
                [
                    'code'=>0,
                    'data'=>$message
                ]
            );


    }

    public function getUserMessageCount(Request $request,MessageService $messageService)
    {
        $validator = Validator::make($request->all(),[
            'user_id'=>'required'
        ]);
        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        $messageCount = 0;

        $messageCount = $messageService->getUserMessageCount($request->user_id);

        return response()->json(
            [
                'code'=>0,
                'data'=>$messageCount
            ]
        );

    }

    public function getUserUnreadMessageCount(Request $request,MessageService $messageService)
    {
        $validator = Validator::make($request->all(),[
            'user_id'=>'required'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        $messageCount = 0;

        $messageCount = $messageService->getUnreadMessagesCount($request->user_id);

        return response()->json(
            [
                'code'=>0,
                'data'=>$messageCount
            ]
        );
    }

    public function deleteOwnMessage(Request $request,MessageService $messageService)
    {
        $validator = Validator::make($request->all(),[
            'user_id'=>'required',
            'message_id'=>'required'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        if (!($messageService->getUserMessagesByMult(['to_id'=>$request->user_id,'id'=>$request->message_id],['id'])))
            throw new NoPermissionException();

        if($messageService->deleteMessage($request->user_id,$request->message_id))
            return response()->json(
                [
                    'code'=>0
                ]
            );
    }

    public function deleteMessage(Request $request,MessageService $messageService,RoleService $roleService)
    {
        $validator = Validator::make($request->all(),[
            'user_id'=>'required',
            'message_id'=>'required',
            'operator_id'=>'required'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }
        //管理员才可以删除别人的消息
        if (!($roleService->hasRole($request->opreator_id,'admin')))
            throw new NoPermissionException();

        if($messageService->deleteMessage($request->user_id,$request->message_id))
            return response()->json(
                [
                    'code'=>0
                ]
            );
    }


}