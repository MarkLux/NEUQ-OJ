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
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\Problem\SolutionNotExistException;
use Illuminate\Http\Request;
use NEUQOJ\Facades\Permission;
use NEUQOJ\Jobs\SendJugdeRequest;
use NEUQOJ\Services\SolutionService;

class SolutionController extends Controller
{
    private $solutionService;

    public function __construct(SolutionService $service)
    {
        $this->solutionService = $service;
        $this->middleware('token')->except('getStatistics');
    }

    public function getSolutions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'size' => 'integer|min:1',
            'problem_id' => 'integer',
            'result' => 'integer|min:-1|max:12',
            'language' => 'integer|min:-1|max:17',
            'user_id' => 'integer'
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $page = $request->input('page', 1);
        $size = $request->input('size', 20);
        $problemId = $request->input('problem_id', -1);
        $result = $request->input('result', -1);
        $language = $request->input('language', -1);

        $condition = [];
        if ($problemId != -1) $condition['problem_id'] = $problemId;
        if ($result != -1) $condition['result'] = $result;
        if ($language != -1) $condition['language'] = $language;


        if (Permission::checkPermission($request->user->id, ['view-solutions'])) {
            $userId = $request->input('user_id', -1);
            if ($userId != -1) $condition['user_id'] = $userId;
        } else {
            $condition['user_id'] = $request->user->id;
        }

        $data = $this->solutionService->getAllSolutions($page, $size, $condition);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function getSolution(int $solutionId)
    {
        $data = $this->solutionService->getSolution($solutionId);

        $data['solution_id'] = $data['id'];
        unset($data['id']);
        unset($data['problemNum']);
        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function getSourceCode(int $solutionId)
    {

        if (!$this->solutionService->isSolutionExist($solutionId))
            throw new SolutionNotExistException();

        $sourceCode = $this->solutionService->getSourceCode($solutionId);

        return response()->json([
            'code' => 0,
            'data' => $sourceCode
        ]);
    }

    /**
     * 重判题目
     */

    public function rejudgeSolution(Request $request)
    {
        if (!Permission::checkPermission($request->user->id,['rejudge-solution'])) {
            throw new NoPermissionException();
        }

        $solutionId = $request->solution;

        $solution = $this->solutionService->getRawSolution($solutionId);
        $sourceCode = $this->solutionService->getSourceCode($solutionId);

        if ($solution == null) {
            throw new SolutionNotExistException();
        }

        $solution = $solution->toArray();

        $solution['source_code'] = $sourceCode->source;

        $this->dispatch(new SendJugdeRequest($solutionId,$solution['problem_id'],$solution,$solution['problem_num'],$solution['user_id'],3));

        return response()->json([
            'code' => 0,
            'data' => [
                'solution_id' => $solutionId
            ]
        ]);
    }

    public function getStatistics(Request $request)
    {
        $days = $request->get('days',12);

        if ($days > 30) {
            $days = 30;
        }

        return response()->json(
            $this->solutionService->getSolutionStatistics($days)
        );
    }

}