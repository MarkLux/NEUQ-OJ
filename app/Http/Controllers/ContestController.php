<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
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

    public function submitProblem(Request $request,int $contestId,int $problemNum)
    {

    }

    public function getRankList(Request $request,int $contestId)
    {

    }
}
