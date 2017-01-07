<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Cache\Repository;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\ProblemGroup\ContestNotExistException;
use NEUQOJ\Http\Requests;
use NEUQOJ\Services\ContestService;
use NEUQOJ\Exceptions\NoPermissionException;

class ContestController extends Controller
{
    private $contestService;

    public function __construct(ContestService $contestService)
    {
        $this->contestService = $contestService;
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
        if(isset($request->user))
            $userId = $request->user->id;
        else
            $userId = -1;

        //检查权限
        if(!$this->contestService->canUserAccessContest($userId,$contestId))
            throw new NoPermissionException();

        $data = $this->contestService->getContest($userId,$contestId);

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
            'size' => 'integer|min:1|max:100'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $page = $request->input('page',1);
        $size = $request->input('size',20);

        if(!$this->contestService->isContestExist($contestId))
            throw new ContestNotExistException();

        $userId = -1;
        if(isset($request->user)) $userId = $request->user->id;

        if(!$this->contestService->canUserAccessContest($userId,$contestId))
            throw new NoPermissionException();

        $solutions = $this->contestService->getStatus($contestId,$page,$size);

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

    public function joinContest(Request $request,int $contestId)
    {

    }

    public function updateContest(Request $request,int $contestId)
    {

    }

    public function getContestAdmission(Request $request,int $contestId)
    {

    }

    public function resetContestAdmission(Request $request,int $contestId)
    {

    }

    public function deleteContest(Request $request,int $contestId)
    {

    }



    public function submitProblem(Request $request,int $contestId,int $problemNum)
    {

    }


}
