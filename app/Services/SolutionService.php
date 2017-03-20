<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-21
 * Time: 下午3:33
 */

namespace NEUQOJ\Services;


use NEUQOJ\Repository\Eloquent\CompileInfoRepository;
use NEUQOJ\Repository\Eloquent\RuntimeInfoRepository;
use NEUQOJ\Repository\Eloquent\SolutionRepository;
use NEUQOJ\Repository\Eloquent\SourceCodeRepository;
use NEUQOJ\Services\Contracts\SolutionServiceInterface;

class SolutionService implements SolutionServiceInterface
{
    private $solutionRepo;
    private $compileInfoRepo;
    private $runtimeInfoRepo;
    private $sourceCodeRepo;

    public function __construct(
        SolutionRepository $solutionRepository,CompileInfoRepository $compileInfoRepository,
        RuntimeInfoRepository $runtimeInfoRepository,SourceCodeRepository $sourceCodeRepository
    )
    {
        $this->solutionRepo = $solutionRepository;
        $this->compileInfoRepo = $compileInfoRepository;
        $this->runtimeInfoRepo = $runtimeInfoRepository;
        $this->sourceCodeRepo = $sourceCodeRepository;
    }

    public function getAllSolutions(int $page, int $size,array $condition)
    {
        return $this->solutionRepo->getAllSolutions($page,$size,$condition);
    }

    public function getSolution(int $solutionId)
    {
        return $this->solutionRepo->getSolution($solutionId);
    }

    public function getSolutionBy(string $param, $value, array $columns = ['*'])
    {
        return $this->solutionRepo->getBy($param,$value,$columns);
    }

    public function getSolutionCount(): int
    {
       return $this->solutionRepo->getTotalCount();
    }

    public function getCompileInfo(int $solutionId)
    {
        return $this->compileInfoRepo->get($solutionId,['*'],'solution_id')->first();
    }

    public function getRuntimeInfo(int $solutionId)
    {
        return $this->runtimeInfoRepo->get($solutionId,['*'],'solution_id')->first();
    }

    public function getSourceCode(int $solutionId)
    {
        return $this->sourceCodeRepo->get($solutionId,['source','private','created_at'],'solution_id')->first();
    }

    public function isSolutionExist(int $solutionId):bool
    {
        $solution = $this->solutionRepo->get($solutionId,['id']);

        if($solution == null)
            return false;
        return true;
    }
}