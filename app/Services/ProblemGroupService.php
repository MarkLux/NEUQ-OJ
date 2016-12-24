<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-24
 * Time: 下午2:20
 */

namespace NEUQOJ\Services;


use NEUQOJ\Repository\Eloquent\ProblemGroupRepository;
use NEUQOJ\Repository\Eloquent\ProblemRepository;
use NEUQOJ\Repository\Eloquent\SolutionRepository;
use NEUQOJ\Services\Contracts\ProblemGroupServiceInterface;

class ProblemGroupService implements ProblemGroupServiceInterface
{
    private $problemGroupRepo;
//    private $problemGroupRelationRepo;
    private $problemRepo;
    private $solutionRepo;

    public function __construct(ProblemGroupRepository $problemGroupRepository, ProblemRepository $problemRepository,SolutionRepository $solutionRepository)
    {
        $this->problemRepo = $problemRepository;
        $this->problemGroupRepo = $problemGroupRepository;
        $this->solutionRepo = $solutionRepository;
    }

    public function getProblemGroup(int $groupId, array $columns = ['*'])
    {
        return $this->problemGroupRepo->get($groupId,$columns)->first();
    }

    public function getProblemGroupBy(string $param, string $value, array $columns = ['*'])
    {
        return $this->problemGroupRepo->getBy($param,$value,$columns);
    }

    public function createProblemGroup(array $data): int
    {
        return $this->problemGroupRepo->insertWithId($data);
    }

    public function deleteProblemGroup(int $groupId): bool
    {
        return $this->problemGroupRepo->deleteWhere(['id' => $groupId]) == 1;
    }

    public function updateProblemGroup(int $groupId, array $data): bool
    {
        return $this->problemGroupRepo->update($data,$groupId) == 1;
    }

    public function isProblemGroupExist(int $groupId): bool
    {
        $problemGroup = $this->problemGroupRepo->get($groupId,['id']);

        return !($problemGroup == null);
    }
}