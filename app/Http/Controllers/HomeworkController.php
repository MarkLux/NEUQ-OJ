<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 17-1-25
 * Time: 下午3:08
 */

namespace NEUQOJ\Http\Controllers;


use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\ProblemGroup\HomeworkNotExistException;
use NEUQOJ\Http\Requests\Request;
use NEUQOJ\Services\HomeworkService;
use NEUQOJ\Services\UserService;

class HomeworkController extends Controller
{
    private $homeworkService;
    private $userService;

    public function  __construct(HomeworkService $homeworkService,UserService $userService)
    {
        $this->homeworkService = $homeworkService;
        $this->userService=$userService;
    }

    public function getAllHomework(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'page' => 'integer|min:1',
            'size' => 'integer|min:1|max:100'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $page = $request->input('page',1);
        $size = $request->input('size',20);

        $data = $this->homeworkService->getAllContests($page,$size);

        $data['code'] = 0;

        return response()->json($data);
    }
    public function getHomeworkIndex(Request $request,int $homeworkId)
    {
        if (!$this->homeworkService->isHomeworkExist($homeworkId))
            throw new HomeworkNotExistException();
        if (isset($request->user))
            $userId = $request->user->id;
        else
            $userId = -1;
        $data = $this->homeworkService->getHomeworkIndex($userId,$homeworkId);

        return response()->json(
            [
                'code' =>0,
                'data'=>$data
            ]
        );
    }

    public function getProblem(Request $request,int $homeworkId,int $problemNum)
    {
        //检查登陆状态和访问权限
        $userId = -1;
        if(isset($request->user)) $userId = $request->user->id;

        if(!$this->homeworkService->canUserAccessContest($userId,$homeworkId))
            throw new NoPermissionException();

        $problem = $this->homeworkService->getProblem($homeworkId,$problemNum);

        return response()->json([
            'code' => 0,
            'data' => $problem
        ]);
    }

    public function getRankList(int $homeworkId)
    {
        if(!$this->homeworkService->isContestExist($homeworkId))
            throw new ContestNotExistException();

        $ranks = $this->homeworkService->getRankList($homeworkId);
        return response()->json([
            'code' => 0,
            'data' => $ranks
        ]);
    }

    public function getStatus(Request $request,int $homeworkId)
    {


        if(!$this->homeworkService->isHomeworkExist($homeworkId))
            throw new ContestNotExistException();

        $userId = -1;
        if(isset($request->user)) $userId = $request->user->id;

        if(!$this->homeworkService->canUserAccessHomework($userId,$homeworkId))
            throw new NoPermissionException();

        $solutions = $this->homeworkService->getHomeworkStatus($userId,$homeworkId);

        $solutions['code'] = 0;

        return response()->json($solutions);
    }
    public function submitProblem(Request $request,int $homeworkId,int $problemNum)
    {
        $validator = Validator::make($request->all(),[
            'source_code' => 'required|string|min:2',
            'private' => 'required|boolean',
            'language' => 'required|integer|min:0|max:17'
        ]);
        if($validator->fails())
            throw new FormValidatorException($request->getMessageBag()->all());

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
        if(!$solutionId)
            throw new InnerError("Fail to Submit :contest ".$contestId." problem ".$problemNum);

        return response()->json([
            'code' => 0,
            'data' => [
                'solution_id' => $solutionId
            ]
        ]);
    }

    public function createHomework(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:100',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'user_Group_id'=>'required|integer',
            'langmask' => 'array',
            'problems' => 'required|array',
            'users' => 'array'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //TODO 检查权限

        $homeworkData = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'creator_id' => $request->user->id,
            'creator_name' => $request->user->name,

            'password' => md5($request->input('password')),
            'langmask' => $request->input('langmask')
        ];
        $problemIds = $request->input('problems');
        $userGroupId = $request->input('user_group_id');

        $users = [];
        if($request->input('users')!=null)
            $users = $request->input('users');

        $homeworkId = $this->homeworkService->addHomework($users,$userGroupId,$homeworkData,$problemIds);

        if($homeworkId == -1)
            throw new InnerError("Fail to create Homework");

        return response()->json([
            'code' => 0,
            'data' => [
                'contest_id' => $homeworkId
            ]
        ]);

    }

   public function updateHomeworkInfo(Request $request,int $homeworkId)
   {
       $validator = Validator::make($request->all(),[
           'title' => 'string|max:100',
           'start_time' => 'date',
           'end_time' => 'date',
           'langmask' => 'array'
       ]);

       if($validator->fails())
           throw new FormValidatorException($validator->getMessageBag()->all());

       if(!$this->homeworkService->isUserHomeworkOwner($request->user->id,$homeworkId))
           throw new NoPermissionException();
       $title = $request->input('title',null);
       $startTime = $request->input('start_time',null);
       $endTime = $request->input('end_time',null);
       $langmask = $request->input('langmask',null);

       $newInfo = [];

       if($title!=null) $newInfo['title'] = $title;
       if($startTime!=null) $newInfo['start_time'] = $startTime;
       if($endTime!=null) $newInfo['end_time'] = $endTime;
       if($langmask!=null) $newInfo['langmask'] = $langmask;

       if(!empty($newInfo))
       {
           if(!$this->homeworkService->updateHomeworkInfo($homeworkId,$newInfo))
               throw new InnerError("Fail to update homework :".$homeworkId);
       }

       return response()->json([
           'code' =>0
       ]);
   }

   public function updateHomeworkProblem(Request $request,int $homeworkId)
   {
       $validator = Validator::make($request->all(),[
           'problem_ids' => 'required|array'
       ]);

       if($validator->fails())
           throw new FormValidatorException($validator->getMessageBag()->all());

       //TODO 检查管理员权限

       if(!$this->homeworkService->isUserHomeworkOwner($request->user->id,$homeworkId))
           throw new NoPermissionException();

       if(!$this->homeworkService->updateHomeworkProblem($homeworkId,$request->problem_ids))
           throw new InnerError("Fail to update Problems in Homework".$homeworkId);
       return response()->json([
           'code'=>0
       ]);


   }

   public function deleteContest(Request $request,int $homeworkId)
   {
       $validator = Validator::make($request->all(),[
           'user' => 'required|array'
       ]);

       if($validator->fails())
           throw new FormValidatorException($validator->getMessageBag()->all());

       if(!$this->homeworkService->isHomeworkExist($homeworkId))
            throw new ContestNotExistException();
       //检查是否为管理员，这里没有使用服务内置方法，可以减少一次查询
       $group = $this->homeworkService->getHomework($homeworkId,['creator_id']);

       if($group->creator_id != $request->user->id)
           throw new NoPermissionException();

       if (!$this->homeworkService->deleteHomework($request->user,$homeworkId))
           throw new InnerError("Fail to delete Homework".$homeworkId);

       return response()->json([
           'code'=>0
       ]);
   }

}
