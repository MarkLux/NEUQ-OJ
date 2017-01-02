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
use NEUQOJ\Services\MessageService;

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

        $message = $messageService->getUserMessages($request->user_id,$request->page,$request->size,['from_id'])

    }
}