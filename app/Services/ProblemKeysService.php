<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-12-21
 * Time: 下午8:38
 */

namespace NEUQOJ\Services;


use NEUQOJ\Repository\Eloquent\ProblemKeyRepository;
use NEUQOJ\Services\Contracts\ProblemKeysServiceInterface;

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

    public function updateProblemKey(array $data):bool
    {
        // TODO: Implement updateProblemKey() method.
    }

    public function getProblemKey(int $problemId)
    {
        // TODO: Implement getProblemKey() method.
    }

}