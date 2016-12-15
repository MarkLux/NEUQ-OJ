<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-14
 * Time: 下午7:02
 */

namespace NEUQOJ\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\TagsExistExceptios;
use NEUQOJ\Exceptions\TagsUnchangedExceptions;
use NEUQOJ\Services\TagsService;

class TagsController extends Controller
{
    public function __construct()
    {

    }

    public function createTag(Request $request,TagsService $tagsService)
    {
       //表单认证
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:45',
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }


        //判断创建的tag是否存在
        $tagId = $tagsService->tagsExisted($request->name);//若不存在id为-1
        if($tagId != -1)
            throw new TagsExistExceptios();

        $data = array(
            'name'=>$request->name
        );

        //创建tag会返回tag的id 创建失败会返回-1
        if(($tagsService->createTags($data))!=-1)
            return response()->json([
                'code' => '0'
            ]);

    }

    public function giveTagsTo(Request $request,TagsService $tagsService)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'tags' => 'required|max:45',
            'problemId'=>'required'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        //判断创建的tag是否存在
        $tagId = $tagsService->tagsExisted($request->name);//若不存在id为-1

        if($tagId != -1)//tag存在时判断这道题是否已经有这个tag
            if($tagsService->hasTags($tagId,$request->problemId))
                throw new TagsExistExceptios();
            else
                $tagsService->giveTagsTo($tagId,$request->problemId);
        else //tag不存在的时候先创建tag获得tag的id，再赋予这道题

            $data = array(
                'name'=>$request->name
            );

            $tagId = $tagsService->createTags($data);
            $tagsService->giveTagsTo($tagId,$request->problemId);

        return response()->json(
            [
                'code'=>'0'
            ]
        );
    }

    function updateTag(Request $request,TagsService $tagsService)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'tagsId' => 'required',
            'tags'=>'required|max:45'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        //判断要修改的tag内容是否存在,或者未改变
        if($tagsService->tagsExisted($request->name))
            throw new TagsExistExceptios();

        if($tagsService->updateTags($request->tagId,$request->tags))
            return response()->json(
                [
                    'code'=> '0'
                ]
            );
    }

    public function updateProblemTag(Request $request,TagsService $tagsService)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'tagId' => 'required',
            'tags'=>'required|max:45',
            'problemId'=>'required'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }


        if(($tagsService->updateProblemTag($request->tagId,$request->problemId,$request->tags)))
            return response()->json([
                'code'=>'0'
            ]);

    }

    public function createProblemTag(Request $request,TagsService $tagsService)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'tags'=>'required|max:45',
            'problemId'=>'required'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        if($tagsService->creatProblemTag($request->problemId,$request->tags))
            return response()->json(
                [
                    'code'=>'0'
                ]
            );
    }

    public function deleteProblemTag(Request $request,TagsService $tagsService)
    {
        $validator = Validator::make($request->all(),[
            'problemId'=>'required',
            'tagId'=>'required'
        ]);

        if($validator->fails())
        {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        if ($tagsService->deleteProblemTag($request->tagId,$request->problemId))
            return response()->json(
                [
                    'code'=>'0'
                ]
            );
    }


}