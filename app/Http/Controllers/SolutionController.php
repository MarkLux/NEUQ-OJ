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
            'size' => 'integer|min:1'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $page = $request->input('page',1);
        $size = $request->input('size',15);

        $data = $this->solutionService->getAllSolutions($page,$size);
        $total_count = $this->solutionService->getSolutionCount();

        return response()->json([
            'code' => 0,
            'data' => $data,
            'total_count' => $total_count
        ]);
    }

    public function getCompileInfo(int $solutionId)
    {
        $compileInfo = $this->solutionService->getCompileInfo($solutionId);

        if($compileInfo==null)
            throw new CompileInfoNotExistException();

        return response()->josn([
            'code' => 0,
            'data' => $compileInfo
        ]);
    }

    public function getRuntimeInfo(int $solutionId)
    {
        $runtimeInfo = $this->solutionService->getRuntimeInfo($solutionId);

        if($runtimeInfo==null)
            throw new RuntimeInfoNotExistException();

        return response()->josn([
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