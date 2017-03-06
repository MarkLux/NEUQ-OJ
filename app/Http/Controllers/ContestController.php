<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Cache\Repository;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\ProblemGroup\ContestNotExistException;
use NEUQOJ\Http\Requests;
use NEUQOJ\Services\ContestService;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Services\UserService;

class ContestController extends Controller
{
    private $contestService;
    private $userService;

    public function __construct(ContestService $contestService,UserService $userService)
    {
        $this->contestService = $contestService;
        $this->userService = $userService;
    }

    public function getAllContests(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'page' => 'integer|min:1',
            'size' => 'integer|min:1|max:100'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $page = $request->input('page',1);
        $size = $request->input('size',20);

        $data = $this->contestService->getAllContests($page,$size);

        $data['code'] = 0;

        return response()->json($data);
    }

    public function getContestIndex(Request $request,int $contestId)
    {
        if(!$this->contestService->isContestExist($contestId))
            throw new ContestNotExistException();

        if(isset($request->user))
            $userId = $request->user->id;
        else
            $userId = -1;

        $data = $this->contestService->getContestIndex($userId,$contestId);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function getProblem(Request $request,int $contestId,int $problemNum)
    {
        //检查登陆状态和访问权限
        $userId = -1;
        if(isset($request->user)) $userId = $request->user->id;

        if(!$this->contestService->canUserAccessContest($userId,$contestId))
            throw new NoPermissionException();

        $problem = $this->contestService->getProblem($contestId,$problemNum);

        return response()->json([
            'code' => 0,
            'data' => $problem
        ]);
    }

    public function getRankList(int $contestId)
    {
        if(!$this->contestService->isContestExist($contestId))
            throw new ContestNotExistException();

        $ranks = $this->contestService->getRankList($contestId);

        return response()->json([
            'code' => 0,
            'data' => $ranks
        ]);
    }

    public function getStatus(Request $request,int $contestId)
    {
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

        if(!$this->contestService->isContestExist($contestId))
            throw new ContestNotExistException();

        $userId = -1;
        if(isset($request->user)) $userId = $request->user->id;

        if(!$this->contestService->canUserAccessContest($userId,$contestId))
            throw new NoPermissionException();

        $solutions = $this->contestService->getStatus($contestId,$page,$size,$condition);

        $solutions['code'] = 0;

        return response()->json($solutions);
    }

    public function searchContest(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'page' => 'integer|min:1',
            'size' => 'integer|min:1|max:100',
            'keyword' => 'required|max:20'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $page = $request->input('page',1);
        $size = $request->input('size',20);
        $keyword = $request->input('keyword');

        $data = $this->contestService->searchContest($keyword,$page,$size);

        $data['code'] = 0;

        return response()->json($data);

    }

    public function submitProblem(Request $request,int $contestId,int $problemNum)
    {
        $validator = Validator::make($request->all(),[
            'source_code' => 'required|string|min:2',
            'private' => 'required|boolean',
            'language' => 'required|integer|min:0|max:17'
        ]);

        if($validator->fails())
            throw new FormValidatorException($request->getMessageBag()->all());

        $data = [
            'source_code' => $request->input('source_code'),
            'private' => $request->input('private'),
            'code_length' => strlen($request->input('source_code')),//好像有点问题
            'ip' => $request->ip(),
            'problem_group_id' => $contestId,
            'language' => $request->input('language'),
            'user_id' => $request->user->id
        ];

        $solutionId = $this->contestService->submitProblem($request->user->id,$contestId,$problemNum,$data);

        if(!$solutionId)
            throw new InnerError("Fail to Submit :contest ".$contestId." problem ".$problemNum);

        return response()->json([
            'code' => 0,
            'data' => [
                'solution_id' => $solutionId
            ]
         ]);
    }

    public function joinContest(Request $request,int $contestId)
    {
        $validator  = Validator::make($request->all(),[
            'password' => 'required|string'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //拿到密码

        if(!$this->contestService->getInContestByPassword($request->user->id,$contestId,$request->password))
            throw new InnerError("Fail to join in Contest");

        return response()->json([
            'code' => 0,
        ]);
    }

    public function createContest(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:100',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'private' => 'required|min:0|max:2',
            'password' => 'string',
            'langmask' => 'array',
            'problems' => 'required|array',
            'users' => 'array'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //TODO 检查权限

        //组装数据

        $contestData = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'creator_id' => $request->user->id,
            'creator_name' => $request->user->name,
            'private' => $request->input('private'),
            'password' => Utils::pwGen($request->input('password')),
            'langmask' => $request->input('langmask')
        ];

        $problemIds = $request->input('problems');


        $users = [];
        if($request->input('users')!=null)
            $users = $request->input('users');

        $contestId = $this->contestService->createContest($contestData,$problemIds,$users);

        if($contestId == -1)
            throw new InnerError("Fail to create contest");

        return response()->json([
            'code' => 0,
            'data' => [
                'contest_id' => $contestId
            ]
        ]);
    }

    //更新界面的面板信息（也是竞赛的详细信息页）,get方法
    public function getUpdatePanel(Request $request,int $contestId)
    {
        //先检查是否是创建者或者管理员
        if(!$this->contestService->isUserContestCreator($request->user->id,$contestId))
            throw new NoPermissionException();

        //TODO：管理员检查

        $data = $this->contestService->getContestDetail($contestId);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    //更新基本信息

    public function updateContestInfo(Request $request,int $contestId)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'string|max:100',
            'start_time' => 'date|after:now',
            'end_time' => 'date|after:start_time',
            'langmask' => 'array',
            'private' => 'integer|min:0|max:2',
            'password' => 'string|min:6|max:20',
            'users' => 'array',
            'user_password' => 'required|string|min:6|max:20'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //为危险动作检查密码
        if(!Utils::pwCheck($request->input('user_password'),$request->user->password))
            throw new PasswordErrorException();
        //TODO 检查是否是管理员
        if(!$this->contestService->isUserContestCreator($contestId,$request->user->id))
            throw new NoPermissionException();

        $title = $request->input('title',null);
        $startTime = $request->input('start_time',null);
        $endTime = $request->input('end_time',null);
        $langmask = $request->input('langmask',null);
        $users = $request->input('users',null);
        $private = $request->input('private',null);
        $password = $request->input('password',null);

        $newInfo = [];

        if($title!=null) $newInfo['title'] = $title;
        if($startTime!=null) $newInfo['start_time'] = $startTime;
        if($endTime!=null) $newInfo['end_time'] = $endTime;
        if($langmask!=null) $newInfo['langmask'] = $langmask;
        if($private!=null) $newInfo['private'] = $private;
        if($password!=null) $newInfo['password'] = Utils::pwGen($password);

        if(!empty($newInfo))
        {
            if(!$this->contestService->updateContestInfo($contestId,$newInfo))
                throw new InnerError("Fail to update contest :".$contestId);
        }

        if($users!=null)
        {
            if(!$this->contestService->resetContestPermission($contestId,$users))
                throw new InnerError("Fail to update contest permission");
        }

        //上面这个其实应该写在一起，用transaction搞定，暂时不想做太大的改动了

        return response()->json([
            'code' => 0
        ]);
    }

    public function updateContestProblem(Request $request,int $contestId)
    {
        $validator = Validator::make($request->all(),[
            'problem_ids' => 'required|array',
            'password' => 'required|string|min:6|max:20'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //密码检查
        if(!Utils::pwCheck($request->input('password'),$request->user->password))
            throw new PasswordErrorException();

        //TODO 检查管理员权限

        if(!$this->contestService->isUserContestCreator($request->user->id,$contestId))
            throw new NoPermissionException();

        //拿到的是所有题目id的集合
        if(!$this->contestService->updateContestProblem($contestId,$request->problem_ids))
            throw new InnerError("Fail to update Problems in contest ".$contestId);

        return response()->json([
            'code' => 0
        ]);
    }

    public function deleteContest(Request $request,int $contestId)
    {
        $validator = Validator::make($request->all(),[
           'password' => 'required'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //TODO 设置管理员权限

        if(!$this->contestService->isContestExist($contestId))
            throw new ContestNotExistException();

        //检查是否为管理员，这里没有使用服务内置方法，可以减少一次查询
        $group = $this->contestService->getContest($contestId,['creator_id']);

        if($group->creator_id != $request->user->id)
            throw new NoPermissionException();

        $user = $this->userService->getUserById($group->creator_id,['password']);

        //验证密码
        if(!Utils::pwCheck($request->input('password'),$user->password))
            throw new PasswordErrorException();

        if(!$this->contestService->deleteContest($contestId))
            throw new InnerError("Fail to delete Contest");

        return response()->json([
            'code' => 0
        ]);
    }

}

