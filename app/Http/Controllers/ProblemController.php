<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-13
 * Time: 下午5:39
 */

namespace NEUQOJ\Http\Controllers;


use Illuminate\Http\Request;
use NEUQOJ\Exceptions\Problem\ProblemNotExistException;
use NEUQOJ\Services\ProblemService;

class ProblemController extends Controller
{
    private $problemService;
    private $roleService;

    public function __construct(ProblemService $service)
    {
        $this->problemService = $service;
    }

    public function getProblem(int $problemId)
    {
        $problem = $this->problemService->getProblemById($problemId);

        //这样处理可以减少一次数据库查询
        if($problem == null)
            throw new ProblemNotExistException();

        return response()->json([
            'code' => 0,
            'problem' => $problem
        ]);
    }

    public function addProblem(Request $request)
    {
        //先做身份验证
    }
}