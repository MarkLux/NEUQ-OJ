<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-13
 * Time: 下午5:39
 */

namespace NEUQOJ\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\Problem\ProblemNotExistException;
use NEUQOJ\Services\ProblemService;

class ProblemController extends Controller
{
    private $problemService;
//    private $roleService;

    public function __construct(ProblemService $service)
    {
        $this->problemService = $service;
    }

    private function getValidateRules()
    {
        return [
            'title' => 'required|max:100',
            'description' => 'required',//题目描述应该是富文本 这里保存的是html代码或者markdown代码
            'difficulty' => 'required|integer|min:1|max:5',
            'sample_output' => 'required',
            'test_output' => 'required',
            'source' => 'max:100',
            'time_limit' => 'required|integer',
            'memory_limit' => 'required|integer|max:512',
            'spj' => 'required|max:1',
            'is_public' => 'required|boolean'
        ];
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
        //表单验证
        $validator = Validator::make($request->all(),$this->getValidateRules());

        //  TODO:  权限验证

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());
        
        //重新组装数据
        
        $problemData = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'difficulty' => $request->input('difficulty'),
            'sample_input'=> $request->input('sample_input'),
            'sample_output' => $request->input('sample_output'),
            'source' => $request->input('source'),
            'time_limit' => $request->input('time_limit'),
            'memory_limit' => $request->input('memory_limit'),
            'hint'=>$request->input('hint'),
            'spj' => $request->input('spj'),
            'is_public' => $request->input('is_public')
        ];

        $testData = [
            'input' => $request->input('test_input'),
            'output' => $request->input('test_output')
        ];

        $id = $this->problemService->addProblem($request->user,$problemData,$testData);

        if($id==-1)
            throw new InnerError("Fail to add problem");

        return response()->json([
            'code' => 0,
            'problem_id' => $id
        ]);
    }
}