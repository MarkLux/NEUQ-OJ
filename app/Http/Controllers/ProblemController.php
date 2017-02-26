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
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\Problem\ProblemNotExistException;
use NEUQOJ\Repository\Models\Token;
use NEUQOJ\Services\ProblemService;
use NEUQOJ\Services\TokenService;

class ProblemController extends Controller
{
    private $problemService;
    private $tokenService;

    public function __construct(ProblemService $problemService,TokenService $tokenService)
    {
        $this->problemService = $problemService;
        $this->tokenService = $tokenService;
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

    public function getProblems(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'page' => 'integer|min:1',
            'size' => 'integer|min:1|max:40'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $page = $request->input('page',1);
        $size = $request->input('size',20);

        $total_count = $this->problemService->getTotalPublicCount();

        $userId = -1;
        //检测用户登陆状态
        if(isset($request->user))
            $userId = $request->user->id;

        if(!empty($total_count))
            $data = $this->problemService->getProblems($userId,$page,$size);
        else
            $data = null;

        return response()->json([
            'code' => 0,
            'data' => $data,
            'total_count' => $total_count
        ]);

    }

    public function getProblem(Request $request,int $problemId)
    {
        $problem = $this->problemService->getProblemById($problemId);

        if($problem == false)//可能出bug
            throw new ProblemNotExistException();

        if($problem['is_public'] == 0)//是私有题目
        {
            if($request->user == null) throw new NoPermissionException(); //没登陆
            elseif(!$this->problemService->canUserAccessProblem($request->user->id,$problemId)) throw new NoPermissionException();
        }

        return response()->json([
            'code' => 0,
            'data' => $problem
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

    public function updateProblem(Request $request,int $problemId)
    {
        //表单验证
        $validator = Validator::make($request->all(),$this->getValidateRules());

        //  TODO:  权限验证

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $problem = $this->problemService->getProblemById($problemId,['creator_id']);

        if($problem == null) throw new ProblemNotExistException();
        elseif($problem->creator_id != $request->user->id) throw new NoPermissionException();

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

        if(!$this->problemService->updateProblem($problemId,$problemData,$testData))
            throw new InnerError("fail to update problem");

        return response()->json([
            'code' => 0
        ]);
    }

    public function submitProblem(Request $request,int $problemId)
    {
        $validator = Validator::make($request->all(),[
            'source_code' => 'required|string|min:2',
            'private' => 'required|boolean',
            'language' => 'required|integer|min:0|max:17'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //TODO: 检查权限

        if(!$this->problemService->isProblemExist($problemId))
            throw new ProblemNotExistException();

        if(!$this->problemService->canUserAccessProblem($request->user->id,$problemId))
            throw new NoPermissionException();

        $data = [
            'source_code' => stripcslashes($request->input('source_code')),
            'private' => $request->input('private'),
            'code_length' => strlen($request->input('source_code')),
            'ip' => $request->ip(),
            'problem_group_id' => $request->input('problem_group_id'),
            'language' => $request->input('language'),
            'user_id' => $request->user->id
        ];

        $solutionId = $this->problemService->submitProblem($problemId,$data);

        if(!$solutionId)
            throw new InnerError("Fail to Submit :problem id".$problemId);

        return response()->json([
            'code' => 0,
            'data' => [
                'solution_id' => $solutionId
            ]
        ]);
    }

    public function getRunData(Request $request,int $problemId)
    {
        $validator = Validator::make($request->all(),[
            'filename' => 'required|string'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //TODO:检查权限

        if(!$this->problemService->isProblemExist($problemId))
            throw new ProblemNotExistException();

        $filePath = $this->problemService->getRunDataPath($problemId,$request->filename);

        if(!empty($filePath))
            return response()->download($filePath);
        else
            throw new FormValidatorException(["wrong param"]);
    }

    public function searchProblems(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'keyword' => 'required|string|min:1|max:20',
            'page' => 'integer|min:1',
            'size' => 'integer|min:1|max:40'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $keyword = $request->input('keyword');
        $page = $request->input('page',1);
        $size = $request->input('size',20);

        $total_count = $this->problemService->searchProblemsCount($keyword);

        $userId = -1;
        if(isset($request->user)) $userId = $request->user->id;

        if($total_count > 0)
            $data = $this->problemService->searchProblems($userId,$keyword,$page,$size);
        else
            $data = null;

        return response()->json([
            'code' => 0,
            'data' => $data,
            'total_count' => $total_count
        ]);
    }

    public function deleteProblem(Request $request,int $problemId)
    {
        $validator = Validator::make($request->all(),[
            'password' => 'required|string|min:6|max:20'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        if(!$this->problemService->isProblemExist($problemId))
            throw new ProblemNotExistException();

        $problem = $this->problemService->getProblemById($problemId);

        //判断是否是创建者
        if($request->user->id != $problem['creator_id'])
            throw new NoPermissionException();

        //TODO ：角色权限检验

        if(!$this->problemService->deleteProblem($request->user,$problemId))
            throw new InnerError("Fail to delete Problem: ".$problemId);

        return response()->json([
            'code' => 0
        ]);

    }
}