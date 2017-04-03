<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-21
 * Time: 下午8:38
 */

namespace NEUQOJ\Services;


use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\ProblemKeyNotExistedException;
use NEUQOJ\Repository\Eloquent\ProblemGroupRelationRepository;
use NEUQOJ\Repository\Eloquent\ProblemKeyRepository;
use NEUQOJ\Repository\Eloquent\ProblemRepository;
use NEUQOJ\Repository\Eloquent\SolutionRepository;
use NEUQOJ\Services\Contracts\ProblemKeysServiceInterface;
use phpDocumentor\Reflection\Types\Null_;

class ProblemKeysService implements ProblemKeysServiceInterface
{
    private $problemKeyRepo;
    private $solutionRepo;
    private $problemGroupRelationRepo;

    public function __construct(ProblemKeyRepository $problemKeyRepository,SolutionRepository $solutionRepository,ProblemGroupRelationRepository $problemGroupRelationRepository)
    {
        $this->problemKeyRepo = $problemKeyRepository;
        $this->solutionRepo = $solutionRepository;
        $this->problemGroupRelationRepo = $problemGroupRelationRepository;
    }

    public function addProblemKey(array $data):bool
   {
        return $this->problemKeyRepo->insert($data);
   }

    public function deleteProblemKey(int $problemId):bool
    {
        return $this->problemKeyRepo->deleteWhere(['problem_id'=>$problemId]);
    }

    public function updateProblemKey(array $condition, array $data):bool
    {
        if ($this->updateProblemKey($condition,$data))
            return true;
        else
            return false;
    }


    public function getProblemKey(int $problemId)
    {

        $data = $this->problemKeyRepo->getBy('problem_id',$problemId);

        if($data == null)
            Throw new ProblemKeyNotExistedException();
        else
            return $data;

    }

    public  function  canUserAccessKey(int $problemId, int $userId)
    {
        //0.判断是否是创建题解的人
        //1.判断题目是否做出来了
        //2.判断题目是否在比赛，作业中
        //3.判断题解是否为可触发类型

        if (!Permission::checkPermission($userId, ['access-any-key']))
        {
            $data = $this->solutionRepo->getByMult(['user_id'=>$userId,'problem_id'=>$problemId,'result'=>4],['id'])->first();
            if ($data == null) {
                if (!($this->problemGroupRelationRepo->get($problemId,['type'])->first()))
                    throw  new  NoPermissionException();

                $keyInfo = $this->problemKeyRepo->get($problemId, ['type','times']);

                if ($keyInfo['type']){
                    if ($this->solutionRepo->getUnPassProblemSolutionCount($userId, $problemId,$keyInfo['key']))
                        return true;
                }
                else
                    throw new NoPermissionException();
            }
            else
                return true;
        }
        else
            return true;

    }

}