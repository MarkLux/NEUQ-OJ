<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-21
 * Time: 下午8:38
 */

namespace NEUQOJ\Services;


use NEUQOJ\Exceptions\ProblemKeyNotExistedException;
use NEUQOJ\Repository\Eloquent\ProblemKeyRepository;
use NEUQOJ\Services\Contracts\ProblemKeysServiceInterface;
use phpDocumentor\Reflection\Types\Null_;

class ProblemKeysService implements ProblemKeysServiceInterface
{
    private $problemKeyRepo;

    public function __construct(ProblemKeyRepository $problemKeyRepository)
    {
        $this->problemKeyRepo = $problemKeyRepository;
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

}