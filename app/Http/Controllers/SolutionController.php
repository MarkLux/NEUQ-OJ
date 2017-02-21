<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-21
 * Time: 下午4:11
 */

namespace NEUQOJ\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\Problem\CompileInfoNotExistException;
use NEUQOJ\Exceptions\Problem\RuntimeInfoNotExistException;
use NEUQOJ\Exceptions\Problem\SolutionNotExistException;
use NEUQOJ\Http\Controllers\Controller;
use Illuminate\Http\Request;
use NEUQOJ\Services\SolutionService;

class SolutionController extends Controller
{
    private $solutionService;

    public function __construct(SolutionService $service)
    {
        $this->solutionService = $service;
    }

    public function getSolutions(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'page' => 'integer|min:1',
            'size' => 'integer|min:1',
            'problem_id' => 'integer',
            'result' => 'integer|min:0|max:12',
            'language' => 'integer|min:0|max:17',
            'user_id' => 'integer'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $page = $request->input('page',1);
        $size = $request->input('size',20);
        $problemId = $request->input('problem_id',-1);
        $result = $request->input('result',-1);
        $language = $request->input('language',-1);
        $userId = $request->input('user_id',-1);

        $condition = [];
        if($problemId != -1) $condition['problem_id'] = $problemId;
        if($result!=-1) $condition['result'] = $result;
        if($language!=-1) $condition['language'] = $language;
        if($userId!=-1) $condition['user_id'] = $userId;

        $data = $this->solutionService->getAllSolutions($page,$size,$condition);
//        $total_count = $this->solutionService->getSolutionCount();
        //数据量太大，如果统计数据总数会导致严重延迟

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function getCompileInfo(int $solutionId)
    {
        $compileInfo = $this->solutionService->getCompileInfo($solutionId);

        if($compileInfo==null)
            throw new CompileInfoNotExistException();

        return response()->json([
            'code' => 0,
            'data' => $compileInfo
        ]);
    }

    public function getRuntimeInfo(int $solutionId)
    {
        $runtimeInfo = $this->solutionService->getRuntimeInfo($solutionId);

        if($runtimeInfo==null)
            throw new RuntimeInfoNotExistException();

        return response()->json([
            'code' => 0,
            'data' => $runtimeInfo
        ]);
    }

    public function getSourceCode(int $solutionId)
    {
        //TODO :检查权限

        if(!$this->solutionService->isSolutionExist($solutionId))
            throw new SolutionNotExistException();

        $sourceCode = $this->solutionService->getSourceCode($solutionId);

        return response()->json([
            'code' => 0,
            'data' => $sourceCode
        ]);
    }
}