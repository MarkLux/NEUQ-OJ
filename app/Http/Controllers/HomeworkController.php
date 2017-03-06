<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17-1-25
 * Time: 下午3:08
 */

namespace NEUQOJ\Http\Controllers;


use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\Problem\ProblemNotExistException;
use NEUQOJ\Exceptions\ProblemGroup\HomeworkNotExistException;
use NEUQOJ\Services\HomeworkService;
use Illuminate\Http\Request;
use NEUQOJ\Services\UserGroupService;
use Illuminate\Support\Facades\Validator;

class HomeworkController extends Controller
{
    private $homeworkService;
    private $userGroupService;

    public function  __construct(HomeworkService $homeworkService,UserGroupService $userGroupService)
    {
        $this->homeworkService = $homeworkService;
        $this->userGroupService=$userGroupService;
    }

    public function addHomework(Request $request,int $groupId)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:100',
            'end_time' => 'required|date|after:now',
            'langmask' => 'array',
            'problems' => 'required|array'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $info = [
            'title' => $request->input('title'),
            'end_time' => $request->input('end_time'),
            'langmask' => $request->input('langmask'),
            'description' => $request->input('description',null)
        ];


        //检查problems数组
        foreach($request->problems as $problem)
        {
            if(!isset($problem['problem_id'])||!isset($problem['problem_score']))
                throw new FormValidatorException(['problems set format error!']);
            elseif(!is_numeric($problem['problem_id']) || !is_numeric($problem['problem_score']))
                throw new FormValidatorException(['problems set format error!']);
        }

        $homeworkId = $this->homeworkService->addHomework($request->user,$groupId,$info,$request->problems);

        //创建
        if($homeworkId == -1)
            throw new InnerError('Fail to add homework');

        return response()->json([
            'code' => 0,
            'homework_id' => $homeworkId
        ]);
    }

    public function getHomeworks(Request $request,int $groupId)
    {
        $validator  = Validator::make($request->all(),[
            'page' => 'integer|min:0',
            'size' => 'integer|min:0|max:100'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $page = $request->input('page',1);
        $size = $request->input('size',20);

        if(!$this->userGroupService->isUserInGroup($request->user->id,$groupId))
            throw new NoPermissionException();

        $homeworks = $this->homeworkService->getHomeworksInGroup($groupId,$page,$size);

        return response()->json([
            'code' => 0,
            'data' => $homeworks
        ]);
    }

    public function getHomeworkIndex(Request $request,int $homeworkId)
    {
        $user = $request->user;

        $data = $this->homeworkService->getHomeworkIndex($user->id,$homeworkId);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function getProblem(Request $request,int $homeworkId,int $problemNum)
    {
        if(!$this->homeworkService->canUserAccessHomework($request->user->id,$homeworkId))
            throw new NoPermissionException();

        $problem = $this->homeworkService->getProblem($homeworkId,$problemNum);

        if($problem == null)
            throw new ProblemNotExistException();

        return response()->json([
            'code' => 0,
            'data' => $problem
        ]);
    }

    public function submitProblem(Request $request,int $homeworkId,int $problemNum)
    {
        $validator = Validator::make($request->all(),[
            'source_code' => 'required|string|min:2',
            'private' => 'required|boolean',
            'language' => 'required|integer|min:0|max:17'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        if(!$this->homeworkService->canUserAccessHomework($request->user->id,$homeworkId))
            throw new NoPermissionException();

        $data = [
            'source_code' => $request->input('source_code'),
            'private' => $request->input('private'),
            'code_length' => strlen($request->input('source_code')),//好像有点问题
            'ip' => $request->ip(),
            'problem_group_id' => $homeworkId,
            'language' => $request->input('language'),
            'user_id' => $request->user->id
        ];

        $solutionId = $this->homeworkService->submitProblem($request->user->id,$homeworkId,$problemNum,$data);

        if($solutionId == -1)
            throw new InnerError("fail to submit problem");

        return response()->json([
            'code' => 0,
            'solution_id' => $solutionId
        ]);
    }

    public function updateHomeworkInfo(Request $request,int $homeworkId)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'string|max:100',
            'end_time' => 'date|after:now',
            'langmask' => 'array',
            'password' => 'required|string|min:6|max:20'
        ]);

        if($validator->fails())
            throw new FormValidatorException();

        //密码检查
        if(!Utils::pwCheck($request->input('password'),$request->user->password))
            throw new PasswordErrorException();

        //检查权限，todo 管理员检查
        if(!$this->homeworkService->isUserHomeworkOwner($request->user->id,$homeworkId))
            throw new NoPermissionException();

        //组装数据

        $title = $request->input('title',null);
        $endTime = $request->input('end_time',null);
        $langmask = $request->input('langmask',null);

        $newInfo = [];

        if($title!=null) $newInfo['title'] = $title;
        if($endTime!=null) $newInfo['end_time'] = $endTime;
        if($langmask!=null) $newInfo['langmask'] = $langmask;

        if(!empty($newInfo))
        {
            if(!$this->homeworkService->updateHomeworkInfo($homeworkId,$newInfo))
                throw new InnerError("fail to update homework: ".$homeworkId);
        }

        return response()->json([
            'code' => 0
        ]);

    }

    public function updateHomeworkProblems(Request $request,int $homeworkId)
    {
        $validator = Validator::make($request->all(),[
            'problems' => 'array|required',
            'password' => 'required|string|min:6|max:20'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $problemInfos = $request->input('problems');

        foreach($problemInfos as $problemInfo)
        {
            if(!isset($problemInfo['problem_id'])||!isset($problemInfo['problem_score']))
                throw new FormValidatorException(['wrong problems format']);
            if(!is_numeric($problemInfo['problem_id'])||!is_numeric($problemInfo['problem_score']))
                throw new FormValidatorException(['wrong problems format']);
        }

        //检查密码
        if(!Utils::pwCheck($request->input('password'),$request->user->password))
            throw new PasswordErrorException();

        //TODO 检查管理员权限

        if(!$this->homeworkService->isUserHomeworkOwner($request->user->id,$homeworkId))
            throw new NoPermissionException();

        if(!$this->homeworkService->updateHomeworkProblem($homeworkId,$problemInfos))
            throw new InnerError("fail to update problems");

        return response()->json([
            'code' => 0
        ]);
    }

    public function getStatus(Request $request,int $homeworkId)
    {
        //写的有点迷

        $validator = Validator::make($request->all(),[
            'page' => 'integer|min:1',
            'size' => 'integer|min:1|max:100',
            'result' => 'integer|min:0|max:12',
            'language' => 'integer|min:0|max:17',
            'user_id' => 'integer',
            'problem_num' => 'integer|min:0'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //检查权限
        if(!$this->homeworkService->canUserAccessHomework($request->user->id,$homeworkId))
            throw new NoPermissionException();

        $page = $request->input('page',1);
        $size = $request->input('size',20);
        $problemNum = $request->input('problem_num',-1);
        $result = $request->input('result',-1);
        $language = $request->input('language',-1);
        $userId = $request->input('user_id',-1);

        $condition = [];
        if($problemNum != -1) $condition['problem_num'] = $problemNum;
        if($result!=-1) $condition['result'] = $result;
        if($language!=-1) $condition['language'] = $language;
        if($userId!=-1) $condition['user_id'] = $userId;

        $solutions = $this->homeworkService->getHomeworkStatus($homeworkId,$page,$size,$condition);

        $solutions['code'] = 0;

        return response()->json($solutions);
    }

    public function getRankList(Request $request,int $homeworkId)
    {
        if(!$this->homeworkService->canUserAccessHomework($request->user->id,$homeworkId))
            throw new NoPermissionException();

        $ranks = $this->homeworkService->getHomeworkRank($homeworkId);

        return response()->json([
            'code' => 0,
            'data' => $ranks
        ]);
    }

    public function deleteHomework(Request $request,int $homeworkId)
    {
        $validator = Validator::make($request->all(),[
            'password' => 'required'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //TODO 设置管理员权限

        if(!$this->homeworkService->isUserHomeworkOwner($request->user->id,$homeworkId))
            throw new NoPermissionException();



        //验证密码
        if(!Utils::pwCheck($request->input('password'),$request->user->password))
            throw new PasswordErrorException();

        if(!$this->homeworkService->deleteHomework($homeworkId))
            throw new InnerError("Fail to delete Homework");

        return response()->json([
            'code' => 0
        ]);
    }
}
