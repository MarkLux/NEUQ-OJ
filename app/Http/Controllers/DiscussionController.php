<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Http\Requests;
use NEUQOJ\Services\DiscussionService;

class DiscussionController extends Controller
{

    private $discussionService;

    public function __construct(DiscussionService $discussionService)
    {
        $this->discussionService = $discussionService;
    }

    public function addTopic(Request $request)
    {
        //表单检查
        $validator = Validator::make($request->all(),[
            'title' => 'required|max:100|string',
            'content' => 'required'
        ]);

        if($validator->failes())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $data = [
            'title' => $request->title,
            'content' => $request->get('content'),
            'problem_id' => $request->problem_id,
            'user_id' => $request->user->id,
        ];

        $topic = $this->discussionService->addTopic($data);

        return response()->json([
            "code" => 0,
            "data" => [
                'problem_id' => $topic->problem_id,
                'user_id' => $topic->user_id,
                'title' => $topic->title
            ],
        ]);
    }

    public function deleteTopic(Request $request,$topicId)
    {
        $userId = $request->user->id;
        $topicId = intval($topicId);

        //检查是否为创帖者
        if($this->discussionService->isTopicCreator($topicId,$userId)) {
            if($this->discussionService->deleteTopic($topicId)) {
                return response()->json([
                    "code" => 0,
                ]);
            }
        } else {
            throw new InnerError("You are not the creator , Fail to delete topic.");
        }

    }

    public function updateTopic(Request $request,$topicId)
    {
        $userId = $request->user->id;
        $topicId = intval($topicId);

        $validator = Validator::make($request->all(),[
            'content' => 'required'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $data = [
            'title' => $request->title,
            'content' => $request->get('content'),
            'problem_id' => $request->problem_id,
            'user_id' => $request->user->id,
        ];

        //检查是否为创帖者
        if($this->discussionService->isTopicCreator($topicId,$userId)) {
            if($this->discussionService->updateTopic($topicId,$data))
                return response()->json([
                    "code" => 0,
                ]);
        } else {
            throw new InnerError("You are not the creator , Fail to update topic.");
        }
    }

    public function searchTopic(Request $request)
    {

    }

    public function addReply(Request $request)
    {

    }

}
