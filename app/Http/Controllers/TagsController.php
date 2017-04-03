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
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\Problem\ProblemNotExistException;
use NEUQOJ\Exceptions\Tag\TagNotExistException;
use NEUQOJ\Exceptions\Tag\TagsExistException;

use NEUQOJ\Facades\Permission;
use NEUQOJ\Services\ProblemService;
use NEUQOJ\Services\TagsService;

class TagsController extends Controller
{

    public function createTags(Request $request, TagsService $tagsService)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'tags' => 'required|array'
        ]);

        if ($validator->fails()) {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        if(!Permission::checkPermission($request->user->id, ['create-problem-tag']))
            throw new NoPermissionException();


        $tags = $request->tags;

        foreach ($tags as $tag){
            $data[] = [
                'name' => $tag
            ];
        }
        //dd($data);
        //创建tag会返回tag的id 创建失败会返回-1
        if (($tagsService->createTags($tags) != -1))
            return response()->json([
                'code' => 0
            ]);
        else
            throw new InnerError();
    }

    public function deleteTag(Request $request, TagsService $tagsService)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'tagId' => 'required',
        ]);

        if ($validator->fails()) {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }
        if(!Permission::checkPermission($request->user->id, ['delete-problem-tag']))
            throw new NoPermissionException();
        //判断要删除的tag是否存在
        if ($tagsService->getTagById($request->tagId) == null)
            throw new TagNotExistException();

        if ($tagsService->deleteTags($request->tagId))
            return response()->json([
                'code' => 0
            ]);
    }

    public function giveTagTo(Request $request, TagsService $tagsService,ProblemService $problemService)//直接用TagId给予问题标签
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'tagId' => 'required',
            'problemId' => 'required'
        ]);

        if ($validator->fails()) {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }
        if(!Permission::checkPermission($request->user->id, ['give-problem-tag']))
            throw new NoPermissionException();

        $tagId = $request->tagId;
        $problemId = $request->problemId;

        $tags = $tagsService->getTagById($request->tagId,['id']);

        if ($tags == null)
            throw new TagNotExistException();

        $problems = $problemService->isProblemExist($problemId);
        if (!$problems)
            throw new ProblemNotExistException();

        if ($tagsService->hasTag($tagId, $request->problemId))//判断这道题是否已经有该标签了
            throw new TagsExistException();


        $tagsService->giveTagTo($tagId, $problemId);

        return response()->json([
            'code' => 0
        ]);
    }

    function updateTag(Request $request, TagsService $tagsService)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'tagId' => 'required',
            'name' => 'required|max:45'
        ]);

        if ($validator->fails()) {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }
        if(!Permission::checkPermission($request->user->id, ['update-problem-tag']))
            throw new NoPermissionException();
        //判断要更新的tag是否存在
        $tag = $tagsService->getTagById($request->tagId,['id']);

        if ($tag == null)
            throw new TagNotExistException();


        //判断要修改的tag内容是否存在
        $tagId = $tagsService->tagsExisted($request->name);
        if ($tagId != $request->tagId)
            if ($tagId != -1)
                throw new TagsExistException();

        if ($tagsService->updateTag($request->tagId, $request->name))
            return response()->json([
                'code' => 0
            ]);
    }

    public function updateProblemTag(Request $request, TagsService $tagsService,ProblemService $problemService)
    {
        //表单认证
        $validator = Validator::make($request->all(), [
            'tagId' => 'required',
            'name' => 'required|max:45',
            'problemId' => 'required'
        ]);

        if ($validator->fails()) {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }
        if(!Permission::checkPermission($request->user->id, ['update-problem-tag']))
            throw new NoPermissionException();
        //判断要更新的tag关系是否存在
        if (!($tagsService->hasTag($request->tagId,$request->problemId))){
            throw new TagNotExistException();
        }

        if (($tagsService->updateProblemTag($request->tagId, $request->problemId, $request->name)))
            return response()->json([
                'code' => 0
            ]);

    }

    public function deleteProblemTag(Request $request, TagsService $tagsService,ProblemService $problemService)
    {
        $validator = Validator::make($request->all(), [
            'problemId' => 'required',
            'tagId' => 'required'
        ]);

        if ($validator->fails()) {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }
        if(!Permission::checkPermission($request->user->id, ['delete-problem-tag']))
            throw new NoPermissionException();

        if ($tagsService->getTagById($request->tagId) == null)
            throw new TagNotExistException();

        if ($problemService->isProblemExist($request->problemId))
            throw new ProblemNotExistException();

        if ($tagsService->deleteProblemTag($request->tagId, $request->problemId))
            return response()->json([
                'code' => 0
            ]);
    }

    public function getSameTagProblem(Request $request, TagsService $tagsService)
    {

        $validator = Validator::make($request->all(), [
            'tagId' => 'integer|required',
            'size' => 'integer|min:1',
            'page' => 'integer|min:1'
        ]);

        if ($validator->fails()) {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        $tagId = $request->input('tagId');
        $size = $request->input('size', 20);
        $page = $request->input('page', 1);

        if ($data = $tagsService->getSameTagProblemList($tagId, $page, $size))
            return response()->json([
                'code' => 0,
                'data' => $data
            ]);
    }

    public function getSameSourceProblem(Request $request, TagsService $tagsService)
    {
        $validator = Validator::make($request->all(), [
            'source' => 'required',
            'size' => 'integer|min:1',
            'page' => 'integer|min:1'
        ]);

        if ($validator->fails()) {
            $data = $validator->getMessageBag()->all();
            throw new FormValidatorException($data);
        }

        $source = $request->input('source');
        $size = $request->input('size', 20);
        $page = $request->input('page', 1);

        if ($data = $tagsService->getSameSourceProblemList($source, $page, $size))
            return response()->json([
                'code' => 0,
                'data' => $data
            ]);
    }
}